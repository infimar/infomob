<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBranchesPhonesSocialsPivotTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('branch_phone');
        Schema::dropIfExists('branch_social');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('branch_phone', function (Blueprint $table) {
            $table->integer('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->integer('phone_id')->unsigned()->index();
            $table->foreign('phone_id')->references('id')->on('phones')->onDelete('cascade');
            $table->primary(['branch_id', 'phone_id']);
        });
        
        Schema::create('branch_social', function (Blueprint $table) {
            $table->integer('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->integer('social_id')->unsigned()->index();
            $table->foreign('social_id')->references('id')->on('socials')->onDelete('cascade');
            $table->primary(['branch_id', 'social_id']);
        });
    }
}
