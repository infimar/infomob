<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socials', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('branch_id')->unsigned();
            $table->string('name', 100);
            $table->enum('type', ['facebook', 'vk', 'googleplus', 'instagram', 'twitter', 'youtube', 'moimir', 'foursquare', 'website', 'linkedin']);
            $table->string('contact_person', 100);
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
        Schema::drop('socials');
    }
}
