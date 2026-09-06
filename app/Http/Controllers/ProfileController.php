<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function profile() : View
    {
        return view('account.profile');
    }

    public function editProfile() : View
    {
        return view('account.edit_profile');
    }

    public function updateProfile() : View
    {
        return view('account.profile');
    }

    public function changePassword()
    {

    }
}
