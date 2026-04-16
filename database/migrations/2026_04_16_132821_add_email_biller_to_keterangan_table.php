<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailBillerToKeteranganTable extends Migration
{
    public function up()
    {
        Schema::table('keterangan', function (Blueprint $table) {
            // Cek apakah kolom sudah ada untuk menghindari error
            if (!Schema::hasColumn('keterangan', 'email_biller')) {
                $table->string('email_biller')->nullable()->after('unitup');
            }
        });
    }

    public function down()
    {
        Schema::table('keterangan', function (Blueprint $table) {
            if (Schema::hasColumn('keterangan', 'email_biller')) {
                $table->dropColumn('email_biller');
            }
        });
    }
}