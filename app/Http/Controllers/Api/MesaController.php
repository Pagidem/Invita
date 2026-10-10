<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;


class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::withCount('guests')
        ->orderBy('numero')
        ->get();

        return response()->json([
            'data' => $mesas,
        ]);
    }
}
