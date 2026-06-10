<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('title', 180);
            $table->text('message');
            $table->string('link')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->boolean('push_enabled')->default(false);
            $table->boolean('email_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('endpoint')->unique();
            $table->text('p256dh');
            $table->text('auth');
            $table->text('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['is_active', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('portal_notifications');
    }
};
