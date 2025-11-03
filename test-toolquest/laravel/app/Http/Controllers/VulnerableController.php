<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VulnerableController extends Controller
{
    public function login(Request $request)
    {
        $user = $request->input('username', '');
        $pass = $request->input('password', '');

        // OPZETTELIJK ONVEILIGE CONCATENATIE (SQL INJECTION KWETSBAAR)
        $sql = "SELECT id, Username, Password, Role FROM logins WHERE Username = '" . $user . "' AND Password = '" . $pass . "' LIMIT 1";

        $rows = DB::select($sql);

        if (count($rows) > 0) {
            $r = $rows[0];
            return response()->json([
                'ok' => true,
                'message' => 'Succesvol ingelogd',
                'user' => [
                    'id' => $r->id,
                    'username' => $r->Username,
                    'role' => $r->Role
                ]
            ]);
        }

        return response()->json(['ok' => false, 'message' => 'Onjuist gebruikersnaam/wachtwoord']);
    }
}
