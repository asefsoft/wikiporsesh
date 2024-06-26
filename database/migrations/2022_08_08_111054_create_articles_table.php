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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('url_id');
            $table->string('slug', 150)->index();
            $table->tinyInteger('total_sections')->unsigned()->index();
            $table->tinyInteger('total_steps')->unsigned()->index();
            $table->string('title_fa',300)->nullable()->fulltext();
            $table->string('title_en',300);
            $table->string('description_fa',500)->fulltext();
            $table->string('description_en',500);
            $table->string('tips_fa',1000)->nullable();
            $table->string('tips_en',1000)->nullable();
            $table->string('warnings_en',1000)->nullable();
            $table->string('warnings_fa',1000)->nullable();
            $table->string('image_url',300)->nullable();
            $table->string('steps_type', 30)->index();
            $table->integer('views')->default(0)->index();
            $table->integer('source_views')->default(null)->index()->comment('number of article views on original site');
            $table->integer('likes')->default(0)->index();
            $table->integer('dislikes')->default(0)->index();
            $table->tinyInteger('rate')->default(0)->index()->comment('between 0 and 100');
            $table->tinyInteger('auto_translated_percent')->default(0);
            $table->boolean('visible')->default(1)->index();
            $table->boolean('is_featured')->default(0)->index();
            $table->boolean('is_translated')->default(0)->index();
            $table->boolean('is_translate_designated')->default(0)->index()->comment("برای ترجمه انتخاب شده است");
            $table->boolean('is_skipped')->default(0)->index();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites');
            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('url_id')->references('id')->on('urls');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
