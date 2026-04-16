<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeteranganTable extends Migration
{
    public function up()
    {
        Schema::create('keterangan', function (Blueprint $table) {
            $table->id();
            $table->string('unitupi')->nullable();
            $table->string('unitap')->nullable();
            $table->string('unitup')->nullable();
            $table->integer('berhasil_didata')->default(0);
            $table->integer('tidak_ada_responden')->default(0);
            $table->integer('responden_menolak')->default(0);
            $table->integer('meteran_tidak_ditemukan')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('keterangan');
    }
}