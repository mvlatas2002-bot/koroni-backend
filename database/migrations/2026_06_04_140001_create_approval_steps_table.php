<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_number');
            $table->string('step_type', 60);
            $table->string('label');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('required_role_code', 60)->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['approval_request_id', 'step_number']);
            $table->index(['approver_id', 'status']);
            $table->index(['required_role_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
