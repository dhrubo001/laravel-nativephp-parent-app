<?php

namespace App\Http\Controllers;

class LoginController extends Controller
{
    public function getLogin()
    {
        return view('pages.login');
    }
}
