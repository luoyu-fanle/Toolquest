<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SQLService;

class SQLController extends Controller
{
    protected $sqlService;

    public function __construct(SQLService $sqlService)
    {
        $this->sqlService = $sqlService;
    }

    function getProfileOverbose(Request $request)
    {
        $id = $request->query('id');
        try {
            $result = $this->sqlService->vulnerableQuery($id);
            if (count($result) > 0) {
                if($request->wantsJson()){
                    return response()->json($result);
                }
                return view('profile', ['result' => $result]);
            } 
            else {
                if($request->wantsJson()){
                    return response()->json(['errormessage' => $result], 404);
                }
                return view('home', ['errormessage' => $result]);
            }
        } catch (\Exception $e) {
                if($request->wantsJson()){
                    return response()->json(["SQL Error message: " => $e->getMessage()], 500);
                }
                return view('home', ["SQL Error message " => $e->getMessage()]);
            }
    }

    function getProfileSilent(Request $request)
    {
        $id = $request->query('id');
        try{
            $result = $this->sqlService->vulnerableQuery($id);
            if (count($result) > 0) {
                if($request->wantsJson()){
                    return response()->json(['result'=> 'User found']);
                }
                return view('profile', [
                    'username' => $result[0]->username,
                    'role'     => $result[0]->role,
                    'id'       => $result[0]->id
                ]);
            } 
            else {
                if($request->wantsJson()){
                    return response()->json(['errormessage' => 'No user found'], 200);
                }
                return view('home', ['errormessage' => 'No user found']);
            }
        } catch (\Exception $e) {
                if($request->wantsJson()){
                    return response()->json(['errormessage' => 'Something went wrong'], 200);
                }
                return view('home', ['errormessage' => 'Something went wrong']);
            }
    }


    // function getProfileSafe(Request $request)
    // {
    //     $id = $request->query('id');
    //     $result = $this->sqlService->safeQuery($id);
    //     if (count($result) > 0) {
    //         if($request->wantsJson()){
    //             return response()->json($result);
    //         }
    //         return view('profile', ['result' => $result]);
    //     } 
    //     else {
    //         if($request->wantsJson()){
    //             return response()->json(['errormessage' => $result], 404);
    //         }
    //         return view('home', ['errormessage' => $result]);
    //     }
    // }
}
