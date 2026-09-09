<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_portal_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('version');
            $table->json('sections');
            $table->boolean('is_active')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'version']);
            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('tenant_legal_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notice_id')->constrained('tenant_portal_notices')->cascadeOnDelete();
            $table->string('notice_version');
            $table->string('status')->default('acknowledged');
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'notice_id']);
            $table->index(['organization_id', 'notice_id']);
            $table->index(['tenant_id', 'notice_version']);
            $table->index(['user_id', 'acknowledged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_legal_acknowledgements');
        Schema::dropIfExists('tenant_portal_notices');
    }
};
