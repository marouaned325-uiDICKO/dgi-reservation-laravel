<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectionController extends Controller
{
    public function index()
    {
        $directions = Direction::with('services')->withCount('services')->orderBy('nom_direction')->get();

        return view('directions.index', compact('directions'));
    }

    public function create()
    {
        return view('directions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_direction' => 'required|string|max:100|unique:directions,nom_direction',
        ], [
            'nom_direction.required' => 'Le nom de la direction est obligatoire.',
            'nom_direction.unique' => 'Cette direction existe déjà.',
        ]);

        Direction::create($validated);

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction ajoutée avec succès. Vous pouvez maintenant lui ajouter des services.');
    }

    public function edit(Direction $direction)
    {
        $direction->load('services');

        return view('directions.edit', compact('direction'));
    }

    public function update(Request $request, Direction $direction)
    {
        $validated = $request->validate([
            'nom_direction' => [
                'required', 'string', 'max:100',
                Rule::unique('directions', 'nom_direction')->ignore($direction->id),
            ],
        ], [
            'nom_direction.required' => 'Le nom de la direction est obligatoire.',
            'nom_direction.unique' => 'Cette direction existe déjà.',
        ]);

        $direction->update($validated);

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction modifiée avec succès.');
    }

    public function destroy(Direction $direction)
    {
        if (User::where('direction_id', $direction->id)->exists()) {
            return redirect()->route('admin.directions.index')
                ->with('error', 'Cette direction ne peut pas être supprimée : des utilisateurs y sont rattachés.');
        }

        $direction->delete(); // ses services sont supprimés avec elle

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction supprimée avec succès.');
    }
}