<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'confirmacion')) {
                $table->string('confirmacion', 20)->default('pendiente')->after('invitations');
            }
        });

        DB::statement("ALTER TABLE guests MODIFY confirmacion ENUM('pendiente', 'confirmado', 'cancelado') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (Schema::hasColumn('guests', 'confirmacion')) {
                $table->dropColumn('confirmacion');
            }
        });
    }
};
