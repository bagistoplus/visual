<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visual_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignable_type', 191);
            $table->unsignedBigInteger('assignable_id');
            $table->string('template_type', 32);
            $table->string('template_key')->nullable();
            $table->string('channel', 100)->nullable();
            $table->string('locale', 20);
            $table->timestamps();

            $table->unique([
                'assignable_type',
                'assignable_id',
                'template_type',
                'channel',
                'locale',
            ], 'visual_template_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visual_template_assignments');
    }
};
