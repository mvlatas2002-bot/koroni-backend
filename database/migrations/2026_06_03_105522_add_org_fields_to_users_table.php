<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('employment_status')->default('active')->after('is_active');
            $table->foreignId('role_id')->nullable()->after('employment_status')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->after('position_id')->constrained('users')->nullOnDelete();
            $table->foreignId('secondary_approver_id')->nullable()->after('manager_id')->constrained('users')->nullOnDelete();
            $table->foreignId('acting_manager_id')->nullable()->after('secondary_approver_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('acting_manager_id');
            $table->dropConstrainedForeignId('secondary_approver_id');
            $table->dropConstrainedForeignId('manager_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['employment_status', 'is_active', 'last_name', 'first_name']);
        });
    }
};
