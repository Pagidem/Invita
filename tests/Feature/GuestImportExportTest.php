<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GuestImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_template_export_returns_excel_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/guests/export-template');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_guest_import_creates_guests_with_tokens(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ci', 'first_name', 'last_name', 'phone', 'email', 'invitations', 'confirmacion', 'notes'],
            ['12345', 'Ana', 'García', '123456789', 'ana@test.com', 2, 'pendiente', 'Primera fila'],
            ['67890', 'Luis', 'Pérez', '987654321', 'luis@test.com', 1, 'confirmado', 'Segunda fila'],
        ], null, 'A1');

        $filePath = tempnam(sys_get_temp_dir(), 'guest-import-') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $response = $this->actingAs($user)->postJson('/api/guests/import', [
            'file' => new UploadedFile(
                $filePath,
                'invitados.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            ),
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('guests', 2);
        $this->assertNotNull(Guest::first()->confirmation_token);
        $this->assertNotNull(Guest::query()->latest('id')->first()->confirmation_token);
    }
}
