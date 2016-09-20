<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferCityPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_city', function (Blueprint $table) {
            $table->integer('offer_id')->unsigned();
            $table->integer('city_id')->unsigned();

            $table->foreign('offer_id')->references('id')->on('offers');
            $table->foreign('city_id')->references('id')->on('cities');

            $table->index(['offer_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('offer_city');
    }
}
