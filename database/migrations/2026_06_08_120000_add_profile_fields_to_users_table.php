<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('emergency_contact_name')->nullable()->after('birth_date');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('profile_notes')->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'birth_date',
                'emergency_contact_name',
                'emergency_contact_phone',
                'profile_notes',
            ]);
        });
    }
};
