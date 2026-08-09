<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'uploaded_by',
    'documentable_type',
    'documentable_id',
    'type',
    'title',
    'file_path',
    'original_name',
    'mime_type',
    'file_size',
    'visible_to_tenant',
])]
class Document extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'file_size' => 'integer',
            'visible_to_tenant' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
