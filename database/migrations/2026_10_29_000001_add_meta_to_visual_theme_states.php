<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a theme's data says about the theme itself, such as the name and the
 * parent of a theme that is a copy of another.
 */
return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('bagisto_visual.storages.database.connection') ?: null;
    }

    public function up(): void
    {
        Schema::table('visual_theme_states', function (Blueprint $table) {
            $table->text('meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('visual_theme_states', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
