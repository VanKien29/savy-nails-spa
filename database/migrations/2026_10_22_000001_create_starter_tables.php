<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_admin')->default(false));
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alt', 500)->default('');
            $table->string('mime');
            $table->string('disk')->default('public');
            $table->string('original_path');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('size');
            $table->string('status')->default('pending')->index();
            $table->json('variants')->nullable();
            $table->timestamps();
        });
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template')->default('sections');
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_home')->default(false)->index();
            $table->boolean('exportable')->default(false);
            $table->boolean('noindex')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('sections')->nullable();
            $table->json('alternates')->nullable();
            $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->json('data');
            $table->timestamps();
        });
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->string('location')->default('header')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('source_page');
            $table->json('utm')->nullable();
            $table->json('extra')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('status')->default('new')->index();
            $table->string('fingerprint', 64)->unique();
            $table->timestamps();
        });
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source')->unique();
            $table->string('target');
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model');
            $table->unsignedBigInteger('record_id');
            $table->string('action');
            $table->json('changes');
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'redirects', 'leads', 'navigation_items', 'site_settings', 'pages', 'media'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
