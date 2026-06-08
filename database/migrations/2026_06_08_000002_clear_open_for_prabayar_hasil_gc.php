<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hasil_gcs', 'jenis_layanan') && Schema::hasColumn('hasil_gcs', 'open')) {
            DB::table('hasil_gcs')
                ->where('jenis_layanan', 'prabayar')
                ->update(['open' => 0]);
        }
    }

    public function down(): void
    {
        //
    }
};
