<?php

use App\Models\SmsCode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_code', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status')->default(SmsCode::STATUS_NEW);
            $table->integer('type');
            $table->integer('user_id');
            $table->integer('code');
            $table->integer('created_at');
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
        Schema::dropIfExists('sms_code');
    }
}
