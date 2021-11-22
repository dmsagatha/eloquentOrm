<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingsTable extends Migration
{
  public function up()
  {
    Schema::create('billings', function (Blueprint $table) {
      $table->id();
      $table->foreignId("user_id")->constrained();
      $table->string("credit_card_number", 20)->unique();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('billings');
  }
}
