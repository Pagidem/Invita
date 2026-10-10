<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mesas')) {

            Schema::create('mesas', function (Blueprint $table) {

                $table->id();

                $table->unsignedTinyInteger('numero')->unique();

                $table->string('color', 50);

                $table->timestamps();
            });
        }

        Schema::table('guests', function (Blueprint $table) {

            if (!Schema::hasColumn('guests', 'mesa_id')) {

                $table->unsignedBigInteger('mesa_id')
                    ->nullable()
                    ->after('email');

                $table->foreign('mesa_id')
                    ->references('id')
                    ->on('mesas')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {

            if (Schema::hasColumn('guests', 'mesa_id')) {

                $table->dropForeign(['mesa_id']);

                $table->dropColumn('mesa_id');
            }
        });

        Schema::dropIfExists('mesas');
    }
};