<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->string('workflow_type', 40)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 40)->default('pending')->index();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('current_step_number')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['requester_id', 'status']);
            $table->index(['current_approver_id', 'status']);
            $table->index(['workflow_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
