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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_category_id')->nullable();
            $table->string('name_fa', 100);
            $table->string('name_en', 100)->unique();
            $table->string('slug', 100)->index()->unique();
            $table->integer('total_sub_categories')->default(0);
            $table->integer('total_articles')->default(0);
            $table->timestamps();

            $table->foreign('parent_category_id')->references('id')->on('categories');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
