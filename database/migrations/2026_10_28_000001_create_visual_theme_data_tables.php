<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Theme data for the database storage driver.
 *
 * Documents hold what the editor is working on. Each publish is a version,
 * and the newest one is what the storefront serves. A theme's state says
 * which version is live and how many edits its editor documents have seen.
 */
return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('bagisto_visual.storages.database.connection');
    }

    public function up(): void
    {
        Schema::create('visual_theme_documents', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100)->default('');
            $table->string('theme', 100);
            $table->string('path', 191);
            $table->longText('content');
            $table->char('hash', 32);
            $table->unsignedInteger('size');
            $table->unsignedBigInteger('stamp');
            $table->timestamps();

            $table->unique(['scope', 'theme', 'path'], 'visual_theme_documents_unique');
        });

        Schema::create('visual_theme_versions', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100)->default('');
            $table->string('theme', 100);
            $table->longText('documents');
            $table->unsignedBigInteger('stamp');
            $table->timestamp('created_at')->nullable();

            $table->index(['scope', 'theme'], 'visual_theme_versions_theme');
        });

        Schema::create('visual_theme_states', function (Blueprint $table) {
            $table->string('scope', 100)->default('');
            $table->string('theme', 100);
            $table->unsignedBigInteger('editor_version')->default(0);
            $table->unsignedBigInteger('live_version_id')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->primary(['scope', 'theme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visual_theme_states');
        Schema::dropIfExists('visual_theme_versions');
        Schema::dropIfExists('visual_theme_documents');
    }
};
