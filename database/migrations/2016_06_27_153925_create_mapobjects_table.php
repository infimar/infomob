<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapobjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapobjects', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['pharmacy', 'atm', 'gas_station', 'other'])->default('other');
            $table->string('icon', 100);
            $table->string('lat', 10);
            $table->string('lng', 10);
            $table->integer('organization_id')->unsigned();
            $table->string('name', 100);
            $table->string('address', 100);
            $table->string('description', 200);
            $table->integer('raion_id')->unsigned();
            $table->integer('hits')->unsigned()->default(0);
            $table->timestamps();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('raion_id')->references('id')->on('raions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mapobjects', function ($table) {
            $table->dropForeign('mapobjects_organization_id_foreign');
            $table->dropForeign('mapobjects_raion_id_foreign');
        });
        Schema::drop('mapobjects');
    }
}
