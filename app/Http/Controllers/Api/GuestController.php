<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportGuestsRequest;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $perPage = (int) $request->input('per_page', 10);

        $perPage = min(max($perPage, 1), 50);

        $guests = Guest::query()->when($search, function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")->
            orWhere('last_name', 'like', "%{$search}%")->
            orWhere('email', 'like', "%{$search}%")->
            orWhere('phone', 'like', "%{$search}%");

        })->orderBy('id')->paginate($perPage)->withQueryString();

        //$guest = Guest::orderBy('id')->paginate(10);

        return GuestResource::collection($guests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuestRequest $request)
    {
        try {
            $guest = Guest::create($request->validated());

            return response()->json([
                'message' => 'Invitado registrado',
                'data' => new GuestResource($guest),
            ], 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'La cédula ya está registrada.',
                ], 409);
            }

            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Guest $guest)
    {
        //return new GuestResource($guest);
        return response()->json([
            'data' => new GuestResource($guest),
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuestRequest $request, Guest $guest)
    {
        $guest->update($request->validated());
        
        return response()->json([
            'message' => 'Invitado actualizado',
            'data' => new GuestResource($guest->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        $guest->delete();

        return response()->json([
            'message' => 'Invitado eliminado',
        ]);
    }

    public function exportTemplate()
    {
        $headers = [
            'ci',
            'first_name',
            'last_name',
            'phone',
            'email',
            'invitations',
            'confirmacion',
            'notes',
        ];

        $rows = [
            $headers,
            ['', '', '', '', '', 1, 'pendiente', ''],
        ];

        $fileName = 'plantilla_invitados.csv';

        return Response::streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function import(ImportGuestsRequest $request)
    {
        $file = $request->file('file');
        $path = $file->getRealPath();

        if (! is_string($path) || ! is_readable($path)) {
            return response()->json([
                'message' => 'No se pudo leer el archivo enviado.',
            ], 422);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->getExtension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } else {
            $handle = fopen($path, 'r');

            if ($handle === false) {
                return response()->json([
                    'message' => 'No se pudo abrir el archivo.',
                ], 422);
            }

            $rows = [];
            $firstLine = fgets($handle);

            if ($firstLine !== false) {
                $delimiter = stripos($firstLine, ';') !== false ? ';' : ',';
                rewind($handle);

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rows[] = $row;
                }
            }

            fclose($handle);
        }

        if ($rows === []) {
            return response()->json([
                'message' => 'El archivo está vacío.',
            ], 422);
        }

        $firstRow = $rows[0];
        $header = array_map(function ($value) {
            $value = preg_replace('/^\xEF\xBB\xBF/u', '', (string) $value);
            $value = strtolower(trim((string) $value));

            return str_replace([';', ','], '', $value);
        }, $firstRow);
        $expectedColumns = ['ci', 'first_name', 'last_name', 'phone', 'email', 'invitations', 'confirmacion', 'notes'];

        if (count($header) < 7) {
            return response()->json([
                'message' => 'La cabecera del archivo no coincide con el formato esperado.',
                'expected' => $expectedColumns,
                'received' => $header,
            ], 422);
        }

        $header = array_pad(array_slice($header, 0, 8), 8, 'notes');

        if ($header !== $expectedColumns) {
            return response()->json([
                'message' => 'La cabecera del archivo no coincide con el formato esperado.',
                'expected' => $expectedColumns,
                'received' => $header,
            ], 422);
        }

        $imported = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $row) {
            if (empty($row) || count(array_filter($row, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $row = array_values($row);
            $row = array_pad(array_slice($row, 0, 8), 8, '');

            $data = [
                'ci' => trim((string) ($row[0] ?? '')),
                'first_name' => trim((string) ($row[1] ?? '')),
                'last_name' => trim((string) ($row[2] ?? '')),
                'phone' => trim((string) ($row[3] ?? '')),
                'email' => trim((string) ($row[4] ?? '')),
                'invitations' => (int) ($row[5] ?? 1),
                'confirmacion' => $this->normalizeConfirmacion((string) ($row[6] ?? 'pendiente')),
                'notes' => trim((string) ($row[7] ?? '')),
            ];

            if ($data['ci'] === '' && $data['first_name'] === '' && $data['last_name'] === '') {
                continue;
            }

            $guest = Guest::query()->updateOrCreate(
                ['ci' => $data['ci']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'invitations' => $data['invitations'],
                    'confirmacion' => $data['confirmacion'],
                    'notes' => $data['notes'],
                ]
            );

            if ($guest->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
        }

        return response()->json([
            'message' => 'Archivo importado correctamente.',
            'imported' => $imported,
            'updated' => $updated,
        ]);
    }

    protected function normalizeConfirmacion(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'confirmado', 'confirmed', 'true', '1', 'yes' => 'confirmado',
            'cancelado', 'cancelled', 'false', '0', 'no' => 'cancelado',
            default => 'pendiente',
        };
    }
}
