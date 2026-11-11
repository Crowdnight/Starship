<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_note_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('viewer_key', 80);
            $table->timestamps();

            $table->unique(['note_id', 'viewer_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_note_views');
    }
};
