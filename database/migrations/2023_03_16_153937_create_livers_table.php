<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livers', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('genre_id');
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('user_id');
            $table->string('titre');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->integer('number_of_pages');
            $table->string('emplacment');
            $table->string('status');
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livers');
    }
};
