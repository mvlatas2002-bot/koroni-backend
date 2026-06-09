<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_authorities', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_type', 40)->index();
            $table->string('authority_type', 60)->index();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('required_role_code', 60)->nullable();
            $table->decimal('min_percent', 5, 2)->nullable();
            $table->decimal('max_percent', 5, 2)->nullable();
            $table->boolean('min_inclusive')->default(true);
            $table->boolean('max_inclusive')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('label')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['workflow_type', 'is_active']);
            $table->index(['department_id', 'workflow_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_authorities');
    }
};
