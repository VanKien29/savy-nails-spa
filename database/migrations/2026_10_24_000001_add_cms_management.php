<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cms_role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
        Schema::table('salon_images', fn (Blueprint $table) => $table->foreignId('media_id')->nullable()->constrained('media')->restrictOnDelete());
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
        Schema::table('salon_images', fn (Blueprint $table) => $table->dropConstrainedForeignId('media_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['cms_role', 'is_active', 'app_authentication_secret', 'app_authentication_recovery_codes']));
    }
};
