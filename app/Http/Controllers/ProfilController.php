<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user(); // ambil user yang sedang login
        return view('profil.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profil.edit', compact('user'));
    }

    public function update(Request $request)
    {
    $user = Auth::user();

    $request->validate([
        'nama' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $user->nama = $request->nama;

    if ($request->hasFile('image')) {
        // Hapus gambar lama
        if ($user->image && file_exists(public_path('foto_karyawan/' . $user->image))) {
            unlink(public_path('foto_karyawan/' . $user->image));
        }

        // Simpan gambar baru
        $file = $request->file('image');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('foto_karyawan'), $filename);
        $user->image = $filename;
    }

    $user->save();

        return redirect()->route('dashboard.index')->with('success', 'Profil berhasil diperbarui.');
    }
}
