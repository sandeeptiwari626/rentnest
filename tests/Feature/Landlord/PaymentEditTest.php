<?php

namespace Tests\Feature\Landlord;

use App\Models\RentPayment;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_landlord_can_edit_a_recorded_payment(): void
    {
        $landlord = User::query()->where('email', 'landlord@rentnest.test')->firstOrFail();
        $payment = RentPayment::query()->where('status', 'pending')->firstOrFail();

        $this->actingAs($landlord)
            ->get(route('landlord.payments.edit', $payment))
            ->assertOk();

        $this->actingAs($landlord)->put(route('landlord.payments.update', $payment), [
            'amount' => 12500,
            'amount_paid' => 12500,
            'due_date' => $payment->due_date->toDateString(),
            'payment_date' => now()->toDateString(),
            'status' => 'paid',
            'payment_method' => 'upi',
            'reference_number' => 'UPI12345678',
            'period_label' => $payment->period_label,
            'notes' => 'Updated after UPI screenshot',
        ])->assertRedirect(route('landlord.payments.show', $payment, absolute: false));

        $payment->refresh();

        $this->assertEquals(12500, (float) $payment->amount);
        $this->assertEquals(12500, (float) $payment->amount_paid);
        $this->assertSame('paid', $payment->status->value);
        $this->assertSame('UPI12345678', $payment->reference_number);
        $this->assertNotNull($payment->receipt_number);
    }

    public function test_landlord_can_upload_payment_proof(): void
    {
        Storage::fake('local');

        $landlord = User::query()->where('email', 'landlord@rentnest.test')->firstOrFail();
        $payment = RentPayment::query()->where('status', 'pending')->firstOrFail();
        $file = UploadedFile::fake()->image('upi-screenshot.jpg', 800, 600);

        $this->actingAs($landlord)->put(route('landlord.payments.update', $payment), [
            'status' => $payment->status->value,
            'amount_paid' => $payment->amount_paid,
            'proof' => $file,
        ]);

        $payment->refresh();

        $this->assertNotNull($payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);

        $this->actingAs($landlord)
            ->get(route('landlord.payments.proof', $payment))
            ->assertOk();
    }
}
