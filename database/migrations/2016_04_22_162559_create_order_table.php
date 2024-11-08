<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_number')->nullable();
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('ship_to')->nullable();
            $table->integer('shipping_zone_id')->unsigned()->nullable();
            $table->integer('shipping_rate_id')->unsigned()->nullable();
            $table->integer('packaging_id')->unsigned()->nullable();
            $table->integer('item_count')->unsigned();
            $table->integer('quantity')->unsigned();

            $table->decimal('taxrate', 20, 6)->nullable();
            $table->decimal('shipping_weight', 20, 6)->nullable();
            $table->decimal('total', 20, 6)->nullable();
            $table->decimal('discount', 20, 6)->nullable();
            $table->decimal('shipping', 20, 6)->nullable();
            $table->decimal('packaging', 20, 6)->nullable();
            $table->decimal('handling', 20, 6)->nullable();
            $table->decimal('taxes', 20, 6)->nullable();
            $table->decimal('grand_total', 20, 6)->nullable();

            // $table->bigInteger('billing_address')->unsigned()->nullable();
            // $table->bigInteger('shipping_address')->unsigned()->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('email')->nullable();
            $table->date('shipping_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('tracking_id')->nullable();
            $table->bigInteger('coupon_id')->unsigned()->nullable();
            $table->integer('carrier_id')->unsigned()->nullable();

            $table->integer('payment_status')->default(1);
            $table->integer('payment_method_id')->unsigned();
            $table->integer('order_status_id')->unsigned()->default(1);

            $table->text('message_to_customer')->nullable();
            $table->boolean('send_invoice_to_customer')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('buyer_note')->nullable();
            $table->boolean('goods_received')->nullable();
            $table->boolean('approved')->nullable();
            $table->boolean('disputed')->nullable();
            $table->bigInteger('feedback_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('ship_to')->references('id')->on('addresses')->onDelete('set null');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->bigInteger('order_id')->unsigned()->index();
            $table->bigInteger('inventory_id')->unsigned()->index()->nullable();
            $table->longText('item_description');
            $table->integer('quantity')->unsigned();
            $table->decimal('unit_price', 20, 6);
            $table->bigInteger('feedback_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')
                ->on('orders')->onDelete('cascade');

            $table->foreign('inventory_id')->references('id')
                ->on('inventories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
}
