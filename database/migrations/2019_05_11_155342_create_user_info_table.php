<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('patronymic')->nullable();
            $table->text('description')->nullable();
            $table->string('activity')->nullable();
            $table->date('birthday')->nullable();
            $table->string('country')->default('');
            $table->string('city')->default('');
            $table->json('geo')->nullable();
            $table->string('language')->default('RU');
            $table->integer('posts')->default(0);
            $table->integer('followers')->default(0);
            $table->integer('following')->default(0);
            $table->timestamps();
        });

        Schema::table('user_info', function(Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_info');
    }
}
