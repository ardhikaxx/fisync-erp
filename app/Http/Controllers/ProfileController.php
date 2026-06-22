<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        \App\Models\System\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => 'Memperbarui profil (Nama/Email/Password)',
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui!');
    }
}
