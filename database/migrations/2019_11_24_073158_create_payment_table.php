<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTable extends Migration
{

    const TABLE_NAME = 'payments';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  
            $table->integer('order_id')->nullable();           
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');          
            $table->string('payment_id');        
            $table->string('payer_id');
            $table->string('payment_method');
            $table->string('country_code');
            $table->string('total_paid');
            $table->string('currency');
            $table->string('status');
            $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(static::TABLE_NAME);
    }
}
