<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'confirmation_token')) {
                $table->uuid('confirmation_token')
                    ->nullable()
                    ->unique()
                    ->after('email');
            }

            if (!Schema::hasColumn('guests', 'companions')) {
                $table->unsignedTinyInteger('companions')
                    ->default(0)
                    ->after('confirmacion');
            }

            if (!Schema::hasColumn('guests', 'confirmed_at')) {
                $table->timestamp('confirmed_at')
                    ->nullable()
                    ->after('companions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('guests', 'confirmation_token')) {
                $columns[] = 'confirmation_token';
            }

            if (Schema::hasColumn('guests', 'companions')) {
                $columns[] = 'companions';
            }

            if (Schema::hasColumn('guests', 'confirmed_at')) {
                $columns[] = 'confirmed_at';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};