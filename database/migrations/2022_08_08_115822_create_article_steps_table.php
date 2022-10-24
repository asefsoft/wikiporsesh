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
        Schema::create('article_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('section_id');
            $table->tinyInteger('order');
            $table->tinyInteger('overall_step_order');
            $table->text('content_fa');
            $table->text('content_en');
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('assets_local')->default(0);

            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('section_id')->references('id')->on('article_sections')->cascadeOnDelete();
            $table->unique(["article_id","section_id", "order"]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_steps');
    }
};
