<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuthenticationModel;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = AuthenticationModel::latest()->paginate(10);
        return view('admin', compact('users'));
    }

    public function create(Request $request)
    {
        $result = AuthenticationModel::create([
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'email'    => $request->input('email'),
            'role'     => $request->input('role')
        ]);

        if ($result) {
            return redirect()->back()->with('message', 'User created successfully.');
            // return view('admin', ['message' => 'User created successfully.']);
        }
        return redirect()->back()->with('error', 'User creation failed.');
        // return view('admin', ['message' => 'User creation failed.']);
    }

    public function delete($id)
    {
        $user = AuthenticationModel::find($id);
        if ($user) {
            $user->delete();
            return redirect()->back()->with('message', 'User deleted successfully.');
            // return view('admin', ['message' => 'User deleted successfully.']);
        }
        return redirect()->back()->with('error', 'User not found.');
        // return view('admin', ['message' => 'User not found.']);
    }

    public function edit(Request $request, $id)
    {
        $user = AuthenticationModel::find($id);
        if ($user) {
            $user->username = $request->input('username', $user->username);
            $user->email    = $request->input('email', $user->email);
            $user->role     = $request->input('role', $user->role);
            if ($request->filled('password')) {
                $user->password = $request->input('password');
        }
            $user->save();
            return redirect()->back()->with('message', 'User updated successfully.');
            // return view('admin', ['message' => 'User updated successfully.']);
        }
        return redirect()->back()->with('error', 'User not found.');
    }

}
