<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id')->unsigned();
            $table->integer('organization_id')->unsigned();
            $table->string('name', 200);
            $table->enum('type',['main', 'custom']);
            $table->text('description')->nullable();
            $table->string('address', 200);
            $table->string('post_index', 20);
            $table->string('email', 100);
            $table->string('lat', 20);
            $table->string('lng', 20);
            $table->string('working_hours', 200)->nullable();
            $table->smallInteger('order')->unsigned()->default(0);
            $table->boolean('is_featured')->default(0);
            $table->integer('hits')->unsigned()->default(0);
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
        Schema::drop('branches');
    }
}
