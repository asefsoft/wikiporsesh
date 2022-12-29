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
        Schema::create('asset_trackers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("trackable_id");
            $table->string("trackable_type");
            $table->string("field_name", 30)->index()->comment('data belongs to which field on target model?');
            $table->string("asset_url", 300);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_trackers');
    }
};
