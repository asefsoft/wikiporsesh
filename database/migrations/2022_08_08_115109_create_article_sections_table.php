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
        Schema::create('article_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->tinyInteger('order');
            $table->string('title_fa', 150)->nullable();
            $table->string('title_en', 150);

            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->unique(["article_id","order"]);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_sections');
    }
};
