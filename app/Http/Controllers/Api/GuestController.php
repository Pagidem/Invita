<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Http\Resources\GuestResource;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;


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

        })->orderByDesc('id')->paginate($perPage)->withQueryString();

        //$guest = Guest::orderBy('id')->paginate(10);

        return GuestResource::collection($guests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuestRequest $request)
    {

        $guest = Guest::create($request->validated());

        return response()->json([
            'message' => 'Invitado registrado',
            'data' => new GuestResource($guest),
        ], 201);
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
}
