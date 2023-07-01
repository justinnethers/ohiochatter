<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::create('forums', function (Blueprint $table) {
//            $table->id();
//            $table->integer('creator_id');
//            $table->string('name', 50);
//            $table->string('slug', 50);
//            $table->string('description');
//            $table->integer('order');
//            $table->string('color');
//            $table->boolean('is_active')->default(true);
//            $table->boolean('is_restricted')->default(false);
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('forums');
    }
}
