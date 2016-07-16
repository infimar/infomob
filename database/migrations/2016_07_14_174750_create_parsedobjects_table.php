<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParsedobjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parsedobjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 400);
            $table->text('categories');
            $table->text('address');
            $table->string('workinghours', 100);
            $table->text('contacts');
            $table->string('sites', 200);
            $table->string('emails', 100);
            $table->text('description');
            $table->text('production');
            $table->string('lat', 50);
            $table->string('lng', 50);
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
        Schema::drop('parsedobjects');
    }
}
