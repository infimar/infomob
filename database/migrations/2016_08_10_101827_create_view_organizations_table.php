<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('view_organizations', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('city_id')->unsigned();
            $table->integer('cat_id')->unsigned();
            $table->string('cat_slug');
            $table->string('cat_name');

            $table->integer('org_id')->unsigned();
            $table->string('org_name');
            $table->string('org_description');
            
            $table->string('org_logo');
            $table->string('org_photo');
            $table->string('org_phones');
            
            $table->integer('order')->unsigned()->default(999999);
            $table->integer('hits')->unsigned()->default(0);
            $table->string('status')->default("published");
            
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
        Schema::drop('view_organizations');
    }
}
