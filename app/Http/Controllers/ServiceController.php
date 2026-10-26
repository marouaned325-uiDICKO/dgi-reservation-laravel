<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /** Un non-administrateur système doit être rattaché à une direction. */
    private function verifierAffectation(): void
    {
        $u = auth()->user();
        abort_if($u->role !== 'admin' && ! $u->direction_id, 403, "Votre compte n'est rattaché à aucune direction.");
    }

    private function directionsAccessibles()
    {
        $u = auth()->user();
        $query = Direction::with('services')->orderBy('nom_direction');

        if ($u->role !== 'admin') {
            $query->where('id', $u->direction_id);
        }

        return $query->get();
    }

    /** Un service d'une autre direction ne peut être ni modifié, ni supprimé. */
    private function autoriser(Service $service): void
    {
        $u = auth()->user();

        abort_unless(
            $u->role === 'admin' || ($u->direction_id && (int) $service->direction_id === (int) $u->direction_id),
            403,
            "Ce service n'appartient pas à votre direction."
        );
    }

    public function index(Request $request)
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $query = Service::with('direction')->orderBy('direction_id')->orderBy('nom_service');

        if ($u->role !== 'admin') {
            $query->where('direction_id', $u->direction_id);
        }

        $services = $query->get();
        $directions = $this->directionsAccessibles();
        $directionChoisie = $u->role === 'admin' ? $request->query('direction') : $u->direction_id;

        return view('services.index', compact('services', 'directions', 'directionChoisie'));
    }

    public function create(Request $request)
    {
        $this->verifierAffectation();
        $u = auth()->user();

        $directions = $this->directionsAccessibles();
        $directionChoisie = $u->role === 'admin' ? $request->query('direction') : $u->direction_id;

        return view('services.create', compact('directions', 'directionChoisie'));
    }

    public function store(Request $request)
    {
        $this->verifierAffectation();
        $u = auth()->user();

        // Hors administrateur : le service est toujours ajouté dans sa propre direction
        if ($u->role !== 'admin') {
            $request->merge(['direction_id' => $u->direction_id]);
        }

        $validated = $request->validate([
            'direction_id' => 'required|exists:directions,id',
            'services' => 'required|array|min:1',
            'services.*' => 'required|string|max:100|distinct',
        ], [
            'direction_id.required' => 'Choisissez la direction de ce service.',
            'direction_id.exists' => 'Cette direction n\'existe pas.',
            'services.required' => 'Ajoutez au moins un service.',
            'services.*.required' => 'Le nom du service est obligatoire.',
            'services.*.distinct' => 'Un même service est saisi deux fois.',
        ]);

        $existants = Service::where('direction_id', $validated['direction_id'])
            ->whereIn('nom_service', $validated['services'])
            ->pluck('nom_service')
            ->all();

        if (! empty($existants)) {
            return back()
                ->withErrors(['services' => 'Déjà présent dans cette direction : ' . implode(', ', $existants) . '.'])
                ->withInput();
        }

        foreach ($validated['services'] as $nom) {
            Service::create([
                'nom_service' => $nom,
                'direction_id' => $validated['direction_id'],
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', count($validated['services']) > 1
                ? 'Services ajoutés avec succès.'
                : 'Service ajouté avec succès.');
    }

    public function edit(Service $service)
    {
        $this->verifierAffectation();
        $this->autoriser($service);

        $directions = $this->directionsAccessibles();

        return view('services.edit', compact('service', 'directions'));
    }

    public function update(Request $request, Service $service)
    {
        $this->verifierAffectation();
        $this->autoriser($service);
        $u = auth()->user();

        if ($u->role !== 'admin') {
            $request->merge(['direction_id' => $u->direction_id]);
        }

        $validated = $request->validate([
            'direction_id' => 'required|exists:directions,id',
            'nom_service' => [
                'required', 'string', 'max:100',
                Rule::unique('services', 'nom_service')
                    ->where('direction_id', $request->input('direction_id'))
                    ->ignore($service->id),
            ],
        ], [
            'direction_id.required' => 'Choisissez la direction de ce service.',
            'nom_service.required' => 'Le nom du service est obligatoire.',
            'nom_service.unique' => 'Ce service existe déjà dans cette direction.',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service modifié avec succès.');
    }

    public function destroy(Service $service)
    {
        $this->verifierAffectation();
        $this->autoriser($service);

        if (User::where('service_id', $service->id)->exists()) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Ce service ne peut pas être supprimé : des utilisateurs y sont rattachés.');
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service supprimé avec succès.');
    }
}