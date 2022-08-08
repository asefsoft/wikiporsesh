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
            $table->string('title_fa',300)->nullable();
            $table->string('title_en',300);
            $table->string('tips_fa',500)->nullable();
            $table->string('tips_en',500)->nullable();
            $table->string('warnings_en',500)->nullable();
            $table->string('warnings_fa',500)->nullable();
            $table->tinyInteger('total_sections');
            $table->boolean('has_steps');
            $table->string('steps_type', 30);
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->tinyInteger('rate')->default(0)->index()->comment('between 0 and 100');
            $table->boolean('visible')->default(1)->index();
            $table->boolean('is_featured')->default(0);
            $table->boolean('is_translated')->default(0);
            $table->timestamp('edited_at')->nullable();
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
