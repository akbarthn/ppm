<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    // Tampilkan semua karyawan
    public function index()
    {
        $karyawans = User::all();
        return view('karyawan.index', compact('karyawans'));
    }

    // Tampilkan form tambah
    public function create()
    {
        return view('karyawan.create');
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'phone' => $request->phone,
            'role' => 'admin'
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan');
    }

    // Tampilkan form edit
    public function edit(User $karyawan)
    {
        return view('karyawan.edit', compact('karyawan'));
    }

    // Update data
    public function update(Request $request, User $karyawan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $karyawan->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $karyawan->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diubah');
    }

    // Hapus data
    public function destroy(User $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus');
    }
}
