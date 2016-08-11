<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderToViewSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('view_subcategories', function (Blueprint $table) {
            $table->integer('order')->unsigned()->default(999999);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('view_subcategories', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
