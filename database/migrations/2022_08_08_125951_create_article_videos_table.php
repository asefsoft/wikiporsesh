<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_videos', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->string('poster_url', 255);
            $table->string('video_url', 255);
            $table->string('title_fa', 150)->nullable();
            $table->string('title_en', 150);
            $table->string('content_fa', 255)->nullable();
            $table->string('content_en', 255);
            $table->boolean('enable')->default(1);
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_videos');
    }
};
