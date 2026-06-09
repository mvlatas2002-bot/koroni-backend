<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('employment_type')->default('internal');
            $table->string('employment_status')->default('active');
            $table->boolean('is_external_collaborator')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('annual_leave_allowance')->default(22);
            $table->timestamps();
        });

        Schema::create('employee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('direct_manager_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->foreignId('secondary_approver_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->foreignId('acting_manager_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['is_primary', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assignments');
        Schema::dropIfExists('employee_profiles');
    }
};
