<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubDapertements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subdapertements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('dapertement_id')->default(0)->unsigned();
            $table->foreign('dapertement_id')->references('id')->on('dapertements')->onUpdate('RESTRICT')->onDelete('RESTRICT');
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
        Schema::dropIfExists('subdapertement');
    }
}
