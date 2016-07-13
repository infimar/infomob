<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToptensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toptens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('organization_id')->unsigned();
            $table->integer('order')->unsigned()->default(9999);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('toptens');
    }
}
