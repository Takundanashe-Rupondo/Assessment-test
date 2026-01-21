<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();

        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Check if user is requesting their own profile or is admin
        if (request()->user()->id != $user->id && request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check if user is updating their own profile or is admin
        if (request()->user()->id != $user->id && request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
        ]);
        
        $user->update($request->all());

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}

