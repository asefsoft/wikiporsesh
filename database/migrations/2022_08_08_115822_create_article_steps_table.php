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
            $table->string('title_fa', 150)->nullable();
            $table->string('title_en', 150);
            $table->text('content_fa');
            $table->text('content_en');
            $table->string('image_url')->nullable();
            $table->boolean('assets_local')->default(0);


            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('section_id')->references('id')->on('article_sections');
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
