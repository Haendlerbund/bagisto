<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartRuleCouponsOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_rule_coupons_order_items', function (Blueprint $table) {
            $table->integer('order_item_id')->unsigned();

            $table->foreign('order_item_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->integer('customer_id')->unsigned();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->integer('cart_rule_coupon_id')->unsigned();

            $table->foreign('cart_rule_coupon_id')
                ->references('id')
                ->on('cart_rule_coupons')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cart_rule_coupons_order_items');
    }
}
