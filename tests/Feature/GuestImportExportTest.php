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

    public function test_guest_template_export_returns_csv_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/guests/export-template');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
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

    public function test_guest_import_accepts_utf8_bom_and_semicolon_delimited_template(): void
    {
        $user = User::factory()->create();

        $csv = "\xEF\xBB\xBFci;first_name;last_name;phone;email;invitations;confirmacion;notes\n12345;Ana;García;123456789;ana@test.com;2;pendiente;Primera fila\n";
        $filePath = tempnam(sys_get_temp_dir(), 'guest-import-') . '.csv';
        file_put_contents($filePath, $csv);

        $response = $this->actingAs($user)->postJson('/api/guests/import', [
            'file' => new UploadedFile(
                $filePath,
                'plantilla_invitados.csv',
                'text/csv',
                null,
                true
            ),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('guests', ['ci' => '12345']);
    }

    public function test_guest_import_accepts_rows_without_trailing_empty_notes_column(): void
    {
        $user = User::factory()->create();

        $csv = "ci;first_name;last_name;phone;email;invitations;confirmacion\n";
        $csv .= "12345;Ana;García;123456789;ana@test.com;2;pendiente\n";
        $csv .= "67890;Luis;Pérez;987654321;luis@test.com;1;confirmado\n";

        $filePath = tempnam(sys_get_temp_dir(), 'guest-import-') . '.csv';
        file_put_contents($filePath, $csv);

        $response = $this->actingAs($user)->postJson('/api/guests/import', [
            'file' => new UploadedFile(
                $filePath,
                'plantilla_invitados.csv',
                'text/csv',
                null,
                true
            ),
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('guests', 2);
    }
}
