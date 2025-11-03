<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProfileController extends Controller
{
    
    public function show($id)
    {
        // GEVAAR: gcheckt niet of het de user is (authentication failure)
        $user = DB::table('logins')->where('id', $id)->first();

        if (!$user) {
            return view('profile', ['username' => 'Onbekende gebruiker']);
        }


        return view('profile', ['username' => $user->Username]);
    }

}
