@extends('layouts.dgi')

@section('title', 'Planning - DGI')

@section('content')
<style>
    .selecteur { margin-bottom: 20px; }
    select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
    .semaine { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
    .jour { background: white; border-radius: 8px; padding: 12px; min-height: 150px; }
    .jour .date-label { font-weight: bold; font-size: 13px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #eee; }
    .creneau { background: #e8f5e9; border-left: 3px solid ##1F6B41; padding: 5px 8px; border-radius: 4px; font-size: 12px; margin-bottom: 6px; }
    .creneau .heure { font-weight: bold; }
    .libre { color: #999; font-size: 12px; text-align: center; padding: 10px 0; }
</style>

<div class="selecteur">
    <form method="GET">
        <label>Salle : </label>
        <select name="salle" onchange="this.form.submit()">
            @foreach ($salles as $salle)
                <option value="{{ $salle->id }}" @selected($salle->id == $salleId)>{{ $salle->nom }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="semaine">
    @foreach ($jours as $jour)
    <div class="jour">
        <div class="date-label">{{ $jour->translatedFormat('D d/m') }}</div>
        @forelse ($reservationsParJour[$jour->format('Y-m-d')] ?? [] as $r)
        <div class="creneau">
            <span class="heure">{{ $r->heure_debut }} - {{ $r->heure_fin }}</span><br>
            {{ $r->motif }}
        </div>
        @empty
        <div class="libre">Libre</div>
        @endforelse
    </div>
    @endforeach
</div>
@endsection