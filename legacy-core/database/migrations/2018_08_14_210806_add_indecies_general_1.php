<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndeciesGeneral1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('last_name');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->index('variant_id');
        });
        Schema::table('tax_invoice', function (Blueprint $table) {
            $table->index('transaction_id');
            $table->index(['taxable_id','taxable_type']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->index(['noteable_id','noteable_type']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index('is_public');
        });

        Schema::table('media_variant', function (Blueprint $table) {
            $table->index('variant_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('type_id');
            $table->index('store_owner_user_id');
        });

        Schema::table('cartlines', function (Blueprint $table) {
            $table->index('item_id');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->index('owner_id');
            $table->index('owner_pid');
            $table->index('type');
        });

        
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_variant_id_index');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_last_name_index');
        });

        Schema::table('tax_invoice', function (Blueprint $table) {
            $table->dropIndex('tax_invoice_transaction_id_index');
            $table->dropIndex('tax_invoice_taxable_id_taxable_type_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_noteable_id_noteable_type_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_is_public_index');
        });

        Schema::table('media_variant', function (Blueprint $table) {
            $table->dropIndex('media_variant_variant_id_index');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_order_id_index');
            $table->dropIndex('invoices_type_id_index');
            $table->dropIndex('invoices_store_owner_user_id_index');
        });
        Schema::table('cartlines', function (Blueprint $table) {
            $table->dropIndex('cartlines_item_id_index');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex('coupons_owner_id_index');
            $table->dropIndex('coupons_owner_pid_index');
            $table->dropIndex('coupons_type_index');
        });
        //
    }
}
