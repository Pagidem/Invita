<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MesaRelationTest extends TestCase
{
    public function test_mesas_table_and_guest_foreign_key_are_created(): void
    {
        $this->assertTrue(Schema::hasTable('mesas'));
        $this->assertTrue(Schema::hasColumn('mesas', 'numero'));
        $this->assertTrue(Schema::hasColumn('mesas', 'color'));
        $this->assertTrue(Schema::hasColumn('guests', 'mesa_id'));
    }
}
