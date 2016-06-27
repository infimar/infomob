<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDescriptionToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE organizations MODIFY description VARCHAR(400) null;');
        DB::statement('ALTER TABLE branches MODIFY description VARCHAR(400) null;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE organizations MODIFY description VARCHAR(400);');
        DB::statement('ALTER TABLE branches MODIFY description VARCHAR(400);');
    }
}
