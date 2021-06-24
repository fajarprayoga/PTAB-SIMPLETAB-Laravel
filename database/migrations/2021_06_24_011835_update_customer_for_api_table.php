<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerForApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `customers` CHANGE `type` `type` ENUM('customer','public') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL  DEFAULT 'customer';");
            \DB::statement("ALTER TABLE `customers` CHANGE `gender` `gender` ENUM('female','male') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL ;");
            \DB::statement("ALTER TABLE `customers` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL ;");
            \DB::statement("ALTER TABLE `customers` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL ;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
