<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard');
    }

    public function selectChilds()
    {
        return view('pages.my-childs');
    }

    public function getNotifications()
    {
        return view('pages.notifications-list');
    }

    public function getProfile()
    {
        return view('pages.profile');
    }
}
