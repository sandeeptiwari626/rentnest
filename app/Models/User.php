<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrganizationRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'avatar_path',
    'current_organization_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<Organization, $this>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps()
            ->withCasts(['role' => OrganizationRole::class]);
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    /**
     * @return HasOne<Tenant, $this>
     */
    public function tenantProfile(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function organizationRole(?Organization $organization = null): ?OrganizationRole
    {
        $organizationId = $organization?->id ?? $this->current_organization_id;

        if ($organizationId === null) {
            return null;
        }

        $membership = $this->relationLoaded('organizations')
            ? $this->organizations->firstWhere('id', $organizationId)
            : $this->organizations()->where('organizations.id', $organizationId)->first();

        if ($membership === null) {
            return null;
        }

        $role = $membership->pivot->role;

        return $role instanceof OrganizationRole
            ? $role
            : OrganizationRole::tryFrom((string) $role);
    }

    public function isLandlord(?Organization $organization = null): bool
    {
        return $this->organizationRole($organization) === OrganizationRole::Landlord;
    }

    public function isTenant(?Organization $organization = null): bool
    {
        return $this->organizationRole($organization) === OrganizationRole::Tenant;
    }
}
