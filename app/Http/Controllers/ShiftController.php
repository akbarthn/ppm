<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        return view('shift.index', compact('shifts'));
    }

    public function create()
    {
        return view('shift.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i',
        ]);

        // Simpan langsung, tanpa perbandingan jam
        Shift::create([
            'name' => $request->name,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('shift.index')->with('success', 'Shift berhasil ditambahkan');
    }


    public function edit(Shift $shift)
    {
        return view('shift.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'start' => 'required|date_format:H:i',
            'end'   => 'required|date_format:H:i|after:start',
        ]);

        $shift->update($request->only(['name', 'start', 'end']));

        return redirect()->route('shift.index')->with('success', 'Shift berhasil diupdate.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('shift.index')->with('success', 'Shift berhasil dihapus.');
    }
}
