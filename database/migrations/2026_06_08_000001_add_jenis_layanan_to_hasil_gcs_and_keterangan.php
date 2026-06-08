<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hasil_gcs', 'jenis_layanan')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->string('jenis_layanan', 20)->default('pascabayar')->after('rbm_id');
            });
        }

        if (!$this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_index')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->index('rbm_id');
            });
        }

        if ($this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_unique')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->dropUnique('hasil_gcs_rbm_id_unique');
            });
        }

        if (!$this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_jenis_layanan_unique')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->unique(['rbm_id', 'jenis_layanan']);
            });
        }

        if (!Schema::hasColumn('keterangan', 'jenis_layanan')) {
            Schema::table('keterangan', function (Blueprint $table) {
                $table->string('jenis_layanan', 20)->default('pascabayar')->after('id');
            });
        }

        if (!$this->indexExists('keterangan', 'keterangan_email_biller_jenis_layanan_index')) {
            Schema::table('keterangan', function (Blueprint $table) {
                $table->index(['email_biller', 'jenis_layanan']);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('keterangan', 'keterangan_email_biller_jenis_layanan_index')) {
            Schema::table('keterangan', function (Blueprint $table) {
                $table->dropIndex('keterangan_email_biller_jenis_layanan_index');
            });
        }

        if (Schema::hasColumn('keterangan', 'jenis_layanan')) {
            Schema::table('keterangan', function (Blueprint $table) {
                $table->dropColumn('jenis_layanan');
            });
        }

        if ($this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_jenis_layanan_unique')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->dropUnique('hasil_gcs_rbm_id_jenis_layanan_unique');
            });
        }

        if (Schema::hasColumn('hasil_gcs', 'jenis_layanan')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->dropColumn('jenis_layanan');
            });
        }

        if (!$this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_unique')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->unique('rbm_id');
            });
        }

        if ($this->indexExists('hasil_gcs', 'hasil_gcs_rbm_id_index')) {
            Schema::table('hasil_gcs', function (Blueprint $table) {
                $table->dropIndex('hasil_gcs_rbm_id_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
    }
};
