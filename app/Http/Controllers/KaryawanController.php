<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = User::all();
        return view('karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => $request->has('role') ? 'required|string|min:6' : 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => $request->has('role') ? Hash::make($request->password) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->has('role') ? 'admin' : null,
        ]);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('foto_karyawan'), $imageName);
            $user->image = $imageName;
            $user->save();
        }

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $karyawan)
    {
        return view('karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => $request->has('role') && $request->filled('password') ? 'required|string|min:6' : 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $karyawan = User::findOrFail($id);
        $karyawan->nama = $request->nama;
        $karyawan->email = $request->email;
        $karyawan->phone = $request->phone;
        $karyawan->address = $request->address;
        $karyawan->role = $request->has('role') ? 'admin' : null;

        if ($request->filled('password')) {
            $karyawan->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($karyawan->image && file_exists(public_path('foto_karyawan/' . $karyawan->image))) {
                unlink(public_path('foto_karyawan/' . $karyawan->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('foto_karyawan'), $imageName);
            $karyawan->image = $imageName;
        }

        $karyawan->save();

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate.');
    }

    public function destroy(User $karyawan)
    {
        if ($karyawan->image && file_exists(public_path('foto_karyawan/' . $karyawan->image))) {
            unlink(public_path('foto_karyawan/' . $karyawan->image));
        }

        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus');
    }
}
