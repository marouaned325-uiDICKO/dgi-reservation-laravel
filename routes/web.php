<?php

use App\Http\Controllers\ActivationCompteController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocaliteController;
use App\Http\Controllers\MotifController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecapitulatifController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\SalleGalerieController;
use App\Http\Controllers\SecteurController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use Illuminate\Support\Facades\Route;

// Galerie des salles = page d'accueil, accessible à tout le monde
Route::get('/', [SalleGalerieController::class, 'index'])->name('salles.galerie');

// Détails d'une salle : accessible sans connexion
Route::get('/salle/{salle}', [SalleGalerieController::class, 'show'])->name('salles.show');

// Lien de connexion reçu par courriel à la création du compte.
// Hors du groupe « auth » : l'utilisateur n'est pas encore connecté quand il clique dessus.
Route::get('/activation/{user}', [ActivationCompteController::class, 'connecter'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('compte.activation');

// Réservations des salles : gestionnaire et administrateur
// (chacun ne voit que les réservations des salles de sa direction)
Route::get('/a-valider', [ValidationController::class, 'index'])
    ->middleware(['auth', 'role:gestionnaire,admin'])
    ->name('validation.index');

Route::get('/a-valider/export', [ValidationController::class, 'exporter'])
    ->middleware(['auth', 'role:gestionnaire,admin'])
    ->name('validation.export');

Route::post('/valider/{reservation}', [ValidationController::class, 'traiter'])
    ->middleware(['auth', 'role:gestionnaire,admin'])
    ->name('validation.traiter');


Route::middleware('auth')->group(function () {

    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Profil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::get('/mot-de-passe/changer', [ChangePasswordController::class, 'show'])
        ->name('mot-de-passe.changer');

    Route::post('/mot-de-passe/changer', [ChangePasswordController::class, 'update'])
        ->name('mot-de-passe.mettre-a-jour');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Mon compte : infos personnelles + mot de passe (tous les rôles)
    Route::get('/mon-compte', [CompteController::class, 'edit'])
        ->name('compte.edit');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // Espace réservation : réserver, mes demandes, liste des salles
    // (agent, gestionnaire et administrateur peuvent réserver)
    Route::middleware('role:agent,gestionnaire,admin')->group(function () {

        Route::get('/reserver', [ReservationController::class, 'create'])
            ->name('reservations.create');

        Route::post('/reserver', [ReservationController::class, 'store'])
            ->name('reservations.store');

        Route::get('/reservation-succes', [ReservationController::class, 'succes'])
            ->name('reservations.succes');

        Route::get('/mes-demandes', [ReservationController::class, 'mesDemandes'])
            ->name('reservations.mes_demandes');

        Route::post('/annuler/{reservation}', [ReservationController::class, 'annuler'])
            ->name('reservations.annuler');

        Route::get('/modifier/{reservation}', [ReservationController::class, 'edit'])
            ->name('reservations.edit');

        Route::put('/modifier/{reservation}', [ReservationController::class, 'update'])
            ->name('reservations.update');

        // Attestation PDF d'une réservation confirmée
        // (droits vérifiés dans AttestationController : demandeur, gestionnaire de la direction, admin)
        Route::get('/reservations/{reservation}/attestation', [AttestationController::class, 'reservation'])
            ->name('reservations.attestation');

        // Salles (galerie pour utilisateurs connectés = liste.blade)
        Route::get('/salles', [SalleGalerieController::class, 'liste'])
            ->name('salles.liste');

        Route::post('/export/tableau', [ExportController::class, 'tableau'])
            ->name('export.tableau');
    });

    // Planning
    Route::get('/planning', [PlanningController::class, 'index'])
        ->name('planning.index');

    // Historique
    Route::get('/historique', [ReservationController::class, 'historique'])
        ->name('historique.index');

    // Pages
    Route::get('/a-propos', [PageController::class, 'aPropos'])
        ->name('pages.a_propos');

    Route::get('/contact', [PageController::class, 'contact'])
        ->name('pages.contact');

    // Salles, équipements et motifs : administrateur et gestionnaire
    // (chacun ne voit que ceux de sa direction ; le contrôle est fait dans les contrôleurs)
    Route::middleware('role:admin,gestionnaire')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('salles', SalleController::class)->except(['show']);

        Route::resource('equipements', EquipementController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('motifs', MotifController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Utilisateurs et services : administrateur
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Réactivation d'un compte expiré (nouveau délai de 3 jours + nouveau lien)
        Route::post('users/{user}/reactiver', [UserController::class, 'reactiver'])->name('users.reactiver');

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('services', ServiceController::class)->except(['show']);
    });

    // Directions et localités : administrateur
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('directions', DirectionController::class)->except(['show']);
        Route::resource('localites', LocaliteController::class)->except(['show']);

        // Récapitulatif par direction
        Route::get('recapitulatif', [RecapitulatifController::class, 'index'])->name('recapitulatif');
    });

    // Secteurs rattachés aux localités (administrateur)
    Route::middleware('role:admin')->group(function () {
        Route::post('/localites/{localite}/secteurs', [SecteurController::class, 'store'])->name('secteurs.store');
        Route::put('/secteurs/{secteur}', [SecteurController::class, 'update'])->name('secteurs.update');
        Route::delete('/secteurs/{secteur}', [SecteurController::class, 'destroy'])->name('secteurs.destroy');
    });
});


require __DIR__.'/auth.php';