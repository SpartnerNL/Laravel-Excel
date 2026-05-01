<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (! Schema::hasColumn('users', 'group_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
        });
    }
}
