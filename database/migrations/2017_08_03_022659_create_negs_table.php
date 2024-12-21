<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNegsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('negs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('negged_id');
            $table->string('negged_type', 50);
            $table->timestamps();

            $table->unique(['user_id', 'negged_id', 'negged_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('negs');
    }
}
