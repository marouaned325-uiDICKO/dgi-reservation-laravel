<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function aPropos()
    {
        return view('pages.a_propos');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}