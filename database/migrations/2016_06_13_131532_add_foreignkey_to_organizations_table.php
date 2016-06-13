<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignkeyToOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function ($table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });
        Schema::table('raions', function ($table) {
            $table->foreign('city_id')->references('id')->on('cities');
        });
        Schema::table('photos', function ($table) {
            $table->foreign('branch_id')->references('id')->on('branches');
        });
        Schema::table('branches', function ($table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('raion_id')->references('id')->on('raions');
        });
        Schema::table('phones', function ($table) {
            $table->foreign('branch_id')->references('id')->on('branches');
        });
        Schema::table('socials', function ($table) {
            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function ($table) {
            $table->dropForeign('organizations_category_id_foreign');
        });
        Schema::table('raions', function ($table) {
            $table->dropForeign('raions_city_id_foreign');
        });
        Schema::table('photos', function ($table) {
            $table->dropForeign('photos_branch_id_foreign');
        });
        Schema::table('branches', function ($table) {
            $table->dropForeign('branches_organization_id_foreign');
            $table->dropForeign('branches_raion_id_foreign');
        });
        Schema::table('phones', function ($table) {
            $table->dropForeign('phones_branch_id_foreign');
        });
        Schema::table('socials', function ($table) {
            $table->dropForeign('socials_branch_id_foreign');
        });
    }
}
