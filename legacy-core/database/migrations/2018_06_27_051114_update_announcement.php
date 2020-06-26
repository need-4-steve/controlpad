<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAnnouncement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('announcements', function (Blueprint $table) {
             $table->dropColumn('customers');
             $table->dropColumn('reps');
             $table->dropColumn('postCategory_id');
             $table->dropColumn('publish_date');
             $table->dropColumn('url');
             $table->dropColumn('public');
             $table->dropColumn('disabled');
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('announcements', function (Blueprint $table) {
             $table->string('url');
             $table->dateTime('publish_date')->nullable();
             $table->integer('postCategory_id');
             $table->boolean('public');
             $table->boolean('reps');
             $table->boolean('customers');
             $table->boolean('disabled');
         });
     }
}
