<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class CompterConnexion
{
    public function handle(Login $event): void
    {
        DB::table('users')
            ->where('id', $event->user->getAuthIdentifier())
            ->increment('login_count');
    }
}