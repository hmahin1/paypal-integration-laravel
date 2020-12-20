<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkrillPaymentsTable extends Migration
{

    const TABLE_NAME = 'skrill_payments';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_id');
            $table->string('mb_transaction_id');
            $table->string('invoice_id');
            $table->string('order_from');
            $table->string('customer_id');
            $table->string('customer_email');
            $table->string('biller_email');
            $table->string('amount');
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
