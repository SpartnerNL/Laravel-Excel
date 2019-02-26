<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->index();
            $table->unsignedInteger('user_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('group_user');
    }
}
