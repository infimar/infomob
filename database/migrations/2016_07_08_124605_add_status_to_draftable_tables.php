<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToDraftableTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->enum('status',['published', 'private', 'draft', 'trashed', 'archived'])->default('draft');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->enum('status',['published', 'private', 'draft', 'trashed', 'archived'])->default('draft');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->enum('status',['published', 'private', 'draft', 'trashed', 'archived'])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
