<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\ExpenseCategory;
use App\Enums\LeaseStatus;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\OrganizationRole;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Lease;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\Organization;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Seed a complete demo organization for local development.
     *
     * Safe to re-run: upserts core records and refreshes related demo data.
     * Demo password for both accounts: password
     */
    public function run(): void
    {
        $now = Carbon::now('Asia/Kolkata');

        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'websprintx-properties'],
            [
                'name' => 'WebSprintX Properties',
                'email' => 'hello@websprintx.com',
                'phone' => '+91 98765 43210',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ]
        );

        $landlord = User::query()->updateOrCreate(
            ['email' => 'landlord@rentnest.test'],
            [
                'name' => 'Sandeep',
                'phone' => '+91 98150 11223',
                'password' => 'password',
                'email_verified_at' => $now,
                'current_organization_id' => $organization->id,
            ]
        );

        $tenantUser = User::query()->updateOrCreate(
            ['email' => 'tenant@rentnest.test'],
            [
                'name' => 'Priya Sharma',
                'phone' => '+91 98720 44556',
                'password' => 'password',
                'email_verified_at' => $now,
                'current_organization_id' => $organization->id,
            ]
        );

        $landlord->forceFill(['current_organization_id' => $organization->id])->save();
        $tenantUser->forceFill(['current_organization_id' => $organization->id])->save();

        foreach ([
            $landlord->id => OrganizationRole::Landlord,
            $tenantUser->id => OrganizationRole::Tenant,
        ] as $userId => $role) {
            if ($organization->users()->where('users.id', $userId)->exists()) {
                $organization->users()->updateExistingPivot($userId, [
                    'role' => $role->value,
                ]);
            } else {
                $organization->users()->attach($userId, [
                    'role' => $role->value,
                ]);
            }
        }

        $tenant = Tenant::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => 'tenant@rentnest.test',
            ],
            [
                'user_id' => $tenantUser->id,
                'name' => 'Priya Sharma',
                'phone' => '+91 98720 44556',
                'notes' => 'Reliable tenant. Prefers UPI for rent. Quiet hours after 10 PM.',
                'emergency_contact_name' => 'Amit Sharma',
                'emergency_contact_phone' => '+91 98140 77889',
            ]
        );

        $property = Property::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => '1BHK Apartment',
            ],
            [
                'type' => PropertyType::Apartment,
                'status' => PropertyStatus::Occupied,
                'address' => '42 Green Valley Road, near Bus Stand',
                'city' => 'Derabassi',
                'state' => 'Punjab',
                'postal_code' => '140507',
                'description' => 'Bright 1BHK flat with balcony, modular kitchen, and covered parking in a calm Derabassi neighbourhood.',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area' => 550,
                'area_unit' => 'sqft',
            ]
        );

        $unit = Unit::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'property_id' => $property->id,
                'name' => 'Unit 1',
            ],
            [
                'status' => PropertyStatus::Occupied,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area' => 550,
                'rent_amount' => 12000,
                'notes' => 'Ground-floor unit facing the courtyard.',
            ]
        );

        $leaseStart = $now->copy()->subMonths(6)->startOfDay();
        $leaseEnd = $now->copy()->addMonths(6)->startOfDay();

        $lease = Lease::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
            ],
            [
                'start_date' => $leaseStart->toDateString(),
                'end_date' => $leaseEnd->toDateString(),
                'monthly_rent' => 12000,
                'security_deposit' => 24000,
                'rent_due_day' => 5,
                'notice_period_days' => 30,
                'status' => LeaseStatus::Active,
                'notes' => 'Standard 12-month residential lease. Rent due on the 5th of each month.',
            ]
        );

        $this->seedRentPayments($organization, $lease, $property, $tenant, $now);
        $this->seedMaintenance($organization, $property, $unit, $tenant, $landlord, $tenantUser, $now);
        $this->seedDocuments($organization, $property, $lease, $landlord, $now);
        $this->seedNotices($organization, $property, $tenant, $landlord, $tenantUser, $now);
        $this->seedExpense($organization, $property, $landlord, $now);
        $this->seedNotifications($landlord, $tenantUser, $now);

        $this->command?->info('Demo data ready.');
        $this->command?->info('  Landlord: landlord@rentnest.test / password');
        $this->command?->info('  Tenant:   tenant@rentnest.test / password');
    }

    private function seedRentPayments(
        Organization $organization,
        Lease $lease,
        Property $property,
        Tenant $tenant,
        Carbon $now
    ): void {
        RentPayment::query()
            ->where('lease_id', $lease->id)
            ->forceDelete();

        $methods = [
            PaymentMethod::Upi,
            PaymentMethod::BankTransfer,
            PaymentMethod::Upi,
            PaymentMethod::BankTransfer,
            PaymentMethod::Upi,
        ];

        // Five historical paid months (previous calendar months).
        for ($i = 5; $i >= 1; $i--) {
            $period = $now->copy()->subMonths($i)->startOfMonth();
            $dueDate = $period->copy()->day(5);
            $paidOn = $dueDate->copy()->addDays(($i % 3));
            $index = 5 - $i;
            $receiptNumber = sprintf('RN-2026-%04d', $index + 1);

            RentPayment::query()->create([
                'organization_id' => $organization->id,
                'lease_id' => $lease->id,
                'property_id' => $property->id,
                'tenant_id' => $tenant->id,
                'amount' => 12000,
                'amount_paid' => 12000,
                'due_date' => $dueDate->toDateString(),
                'payment_date' => $paidOn->toDateString(),
                'payment_method' => $methods[$index],
                'reference_number' => $methods[$index] === PaymentMethod::Upi
                    ? 'UPI'.fake()->numerify('########')
                    : 'NEFT'.fake()->numerify('########'),
                'status' => PaymentStatus::Paid,
                'period_label' => $period->format('F Y'),
                'notes' => 'Received on time.',
                'receipt_number' => $receiptNumber,
            ]);
        }

        // Current month pending (due on rent_due_day).
        $currentPeriod = $now->copy()->startOfMonth();
        RentPayment::query()->create([
            'organization_id' => $organization->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'amount' => 12000,
            'amount_paid' => 0,
            'due_date' => $currentPeriod->copy()->day(5)->toDateString(),
            'payment_date' => null,
            'payment_method' => null,
            'reference_number' => null,
            'status' => PaymentStatus::Pending,
            'period_label' => $currentPeriod->format('F Y'),
            'notes' => 'Awaiting payment.',
            'receipt_number' => null,
        ]);

        // One late (overdue unpaid) sample from six months ago.
        $latePeriod = $now->copy()->subMonths(6)->startOfMonth();
        RentPayment::query()->create([
            'organization_id' => $organization->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'amount' => 12000,
            'amount_paid' => 0,
            'due_date' => $latePeriod->copy()->day(5)->toDateString(),
            'payment_date' => null,
            'payment_method' => null,
            'reference_number' => null,
            'status' => PaymentStatus::Late,
            'period_label' => $latePeriod->format('F Y').' (demo late)',
            'notes' => 'Demo overdue entry for late-payment workflows.',
            'receipt_number' => null,
        ]);
    }

    private function seedMaintenance(
        Organization $organization,
        Property $property,
        Unit $unit,
        Tenant $tenant,
        User $landlord,
        User $tenantUser,
        Carbon $now
    ): void {
        MaintenanceRequest::query()
            ->where('organization_id', $organization->id)
            ->where('title', 'Kitchen sink leaking')
            ->each(function (MaintenanceRequest $request): void {
                $request->comments()->delete();
                $request->forceDelete();
            });

        $reportedAt = $now->copy()->subDays(3)->setTime(10, 15);
        $acknowledgedAt = $now->copy()->subDays(2)->setTime(11, 40);
        $inProgressAt = $now->copy()->subDay()->setTime(9, 5);

        $request = MaintenanceRequest::query()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'reported_by' => $tenantUser->id,
            'title' => 'Kitchen sink leaking',
            'description' => 'Water drips steadily under the kitchen sink whenever the tap is used. Cabinet base is getting damp.',
            'category' => MaintenanceCategory::Plumbing,
            'priority' => MaintenancePriority::High,
            'status' => MaintenanceStatus::InProgress,
            'acknowledged_at' => $acknowledgedAt,
            'scheduled_at' => $now->copy()->addDay()->setTime(16, 0),
            'created_at' => $reportedAt,
            'updated_at' => $inProgressAt,
        ]);

        MaintenanceComment::query()->insert([
            [
                'maintenance_request_id' => $request->id,
                'user_id' => $tenantUser->id,
                'body' => 'Reported a kitchen sink leak under the basin. Please send a plumber.',
                'event_type' => 'status_change',
                'from_status' => null,
                'to_status' => MaintenanceStatus::Open->value,
                'created_at' => $reportedAt,
                'updated_at' => $reportedAt,
            ],
            [
                'maintenance_request_id' => $request->id,
                'user_id' => $landlord->id,
                'body' => 'Acknowledged. Plumbing vendor contacted; visit will be scheduled shortly.',
                'event_type' => 'status_change',
                'from_status' => MaintenanceStatus::Open->value,
                'to_status' => MaintenanceStatus::Acknowledged->value,
                'created_at' => $acknowledgedAt,
                'updated_at' => $acknowledgedAt,
            ],
            [
                'maintenance_request_id' => $request->id,
                'user_id' => $landlord->id,
                'body' => 'Plumber assigned. Work is in progress — expecting completion by tomorrow evening.',
                'event_type' => 'status_change',
                'from_status' => MaintenanceStatus::Acknowledged->value,
                'to_status' => MaintenanceStatus::InProgress->value,
                'created_at' => $inProgressAt,
                'updated_at' => $inProgressAt,
            ],
        ]);
    }

    private function seedDocuments(
        Organization $organization,
        Property $property,
        Lease $lease,
        User $landlord,
        Carbon $now
    ): void {
        Document::query()
            ->where('organization_id', $organization->id)
            ->forceDelete();

        $disk = Storage::disk('local');
        $baseDir = 'documents/'.$organization->id;

        $files = [
            [
                'name' => 'lease-agreement.txt',
                'contents' => "RentNest demo lease agreement\nProperty: 1BHK Apartment, Derabassi\nTenant: Priya Sharma\nMonthly rent: INR 12,000\n",
                'type' => DocumentType::LeaseAgreement,
                'title' => 'Signed lease agreement',
                'documentable' => $lease,
                'visible' => true,
            ],
            [
                'name' => 'tenant-id-note.txt',
                'contents' => "Demo ID proof placeholder for Priya Sharma.\nAadhaar / PAN on file with landlord.\n",
                'type' => DocumentType::Identity,
                'title' => 'Tenant ID copy (demo)',
                'documentable' => $lease,
                'visible' => false,
            ],
            [
                'name' => 'property-overview.txt',
                'contents' => "Property overview — 1BHK Apartment, Derabassi (140507).\nArea: 550 sqft. Amenities: covered parking, 24x7 water.\n",
                'type' => DocumentType::Property,
                'title' => 'Property overview',
                'documentable' => $property,
                'visible' => true,
            ],
        ];

        foreach ($files as $file) {
            $path = $baseDir.'/'.$file['name'];
            $disk->put($path, $file['contents']);

            Document::query()->create([
                'organization_id' => $organization->id,
                'uploaded_by' => $landlord->id,
                'documentable_type' => $file['documentable']::class,
                'documentable_id' => $file['documentable']->id,
                'type' => $file['type'],
                'title' => $file['title'],
                'file_path' => $path,
                'original_name' => $file['name'],
                'mime_type' => 'text/plain',
                'file_size' => strlen($file['contents']),
                'visible_to_tenant' => $file['visible'],
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(20),
            ]);
        }
    }

    private function seedNotices(
        Organization $organization,
        Property $property,
        Tenant $tenant,
        User $landlord,
        User $tenantUser,
        Carbon $now
    ): void {
        Notice::query()
            ->where('organization_id', $organization->id)
            ->each(function (Notice $notice): void {
                $notice->reads()->delete();
                $notice->forceDelete();
            });

        $readNotice = Notice::query()->create([
            'organization_id' => $organization->id,
            'created_by' => $landlord->id,
            'property_id' => $property->id,
            'tenant_id' => null,
            'title' => 'Water supply maintenance',
            'message' => 'Municipal water line work is scheduled this Sunday from 9 AM to 1 PM. Please store water in advance.',
            'publish_date' => $now->copy()->subDays(10)->toDateString(),
            'expiry_date' => $now->copy()->addDays(5)->toDateString(),
        ]);

        NoticeRead::query()->create([
            'notice_id' => $readNotice->id,
            'user_id' => $tenantUser->id,
            'read_at' => $now->copy()->subDays(9),
        ]);

        Notice::query()->create([
            'organization_id' => $organization->id,
            'created_by' => $landlord->id,
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'title' => 'Rent reminder — August',
            'message' => 'Friendly reminder that rent for August is due on the 5th. You can pay via UPI or bank transfer and upload the reference in the app.',
            'publish_date' => $now->copy()->subDays(2)->toDateString(),
            'expiry_date' => $now->copy()->addDays(20)->toDateString(),
        ]);
    }

    private function seedExpense(
        Organization $organization,
        Property $property,
        User $landlord,
        Carbon $now
    ): void {
        Expense::query()
            ->where('organization_id', $organization->id)
            ->where('description', 'like', '%Kitchen sink plumbing repair%')
            ->forceDelete();

        Expense::query()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'recorded_by' => $landlord->id,
            'category' => ExpenseCategory::Repairs,
            'amount' => 2500,
            'expense_date' => $now->copy()->subDay()->toDateString(),
            'vendor' => 'Singh Plumbing Works',
            'description' => 'Kitchen sink plumbing repair — parts and labour.',
        ]);
    }

    private function seedNotifications(User $landlord, User $tenantUser, Carbon $now): void
    {
        if (! Schema::hasTable('notifications')) {
            $this->command?->warn('Skipping notification seeds: notifications table not found.');

            return;
        }

        DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', [$landlord->id, $tenantUser->id])
            ->delete();

        $rows = [
            [
                'user' => $landlord,
                'type' => 'App\\Notifications\\MaintenanceUpdatedNotification',
                'data' => [
                    'title' => 'Maintenance in progress',
                    'message' => 'Kitchen sink leaking is now in progress.',
                    'type' => 'maintenance_updated',
                    'url' => '/landlord/maintenance',
                ],
                'read_at' => null,
                'created_at' => $now->copy()->subHours(6),
            ],
            [
                'user' => $landlord,
                'type' => 'App\\Notifications\\RentReceivedNotification',
                'data' => [
                    'title' => 'Rent received',
                    'message' => 'Priya Sharma paid rent for July 2026.',
                    'type' => 'rent_received',
                    'url' => '/landlord/payments',
                ],
                'read_at' => $now->copy()->subDays(3),
                'created_at' => $now->copy()->subDays(3),
            ],
            [
                'user' => $landlord,
                'type' => 'App\\Notifications\\LeaseExpiryNotification',
                'data' => [
                    'title' => 'Lease approaching end',
                    'message' => 'Lease for 1BHK Apartment / Unit 1 ends in about 6 months.',
                    'type' => 'lease_expiry',
                    'url' => '/landlord/leases',
                ],
                'read_at' => null,
                'created_at' => $now->copy()->subDay(),
            ],
            [
                'user' => $tenantUser,
                'type' => 'App\\Notifications\\NewNoticeNotification',
                'data' => [
                    'title' => 'New notice',
                    'message' => 'Rent reminder — August has been published.',
                    'type' => 'new_notice',
                    'url' => '/tenant/notices',
                ],
                'read_at' => null,
                'created_at' => $now->copy()->subDays(2),
            ],
            [
                'user' => $tenantUser,
                'type' => 'App\\Notifications\\RentReminderNotification',
                'data' => [
                    'title' => 'Rent due soon',
                    'message' => 'Your rent of ₹12,000 for August 2026 is due on the 5th.',
                    'type' => 'rent_reminder',
                    'url' => '/tenant/payments',
                ],
                'read_at' => null,
                'created_at' => $now->copy()->subDays(4),
            ],
            [
                'user' => $tenantUser,
                'type' => 'App\\Notifications\\DocumentUploadedNotification',
                'data' => [
                    'title' => 'New document',
                    'message' => 'Signed lease agreement is available to download.',
                    'type' => 'document_uploaded',
                    'url' => '/tenant/documents',
                ],
                'read_at' => $now->copy()->subDays(15),
                'created_at' => $now->copy()->subDays(20),
            ],
        ];

        foreach ($rows as $row) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => $row['type'],
                'notifiable_type' => User::class,
                'notifiable_id' => $row['user']->id,
                'data' => json_encode($row['data']),
                'read_at' => $row['read_at'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }
    }
}
