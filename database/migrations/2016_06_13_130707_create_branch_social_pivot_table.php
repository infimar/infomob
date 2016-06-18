<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchSocialPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_social', function (Blueprint $table) {
            $table->integer('branch_id')->unsigned()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->integer('social_id')->unsigned()->index();
            $table->foreign('social_id')->references('id')->on('socials')->onDelete('cascade');
            $table->primary(['branch_id', 'social_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('branch_social');
    }
}
