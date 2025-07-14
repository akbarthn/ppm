<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheadules;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Ambil semua shift
        $shifts = Shift::all();

        // Ambil semua jadwal hari ini dan group by shift
        $jadwals = Scheadules::with(['user', 'shift'])
            ->where('date_schedule', $today)
            ->get()
            ->groupBy('id_shift');

        // Siapkan array untuk menampilkan semua shift, meskipun tidak ada data jadwalnya
        $data = collect();

        foreach ($shifts as $shift) {
            $items = $jadwals->get($shift->id, collect()); // default kosong jika tidak ada jadwal
            $data->push([
                'shift' => $shift,
                'items' => $items,
            ]);
        }

        return view('absensi.index', [
            'jadwal' => $data,
            'today' => $today,
        ]);
    }

    public function scan($shiftId)
    {
        return view('absensi.scan', compact('shiftId'));
    }

    public function checkin(Request $request)
    {
        $user = User::where('image', $request->image)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'msg' => 'Wajah tidak dikenali.'
            ]);
        }

        $jadwal = Scheadules::where('date_schedule', Carbon::today()->toDateString())
            ->where('id_user', $user->id)
            ->where('id_shift', $request->shift_id)
            ->first();

        if ($jadwal) {
            $jadwal->check_in = now()->format('H:i:s');
            $jadwal->keterangan = null;
            $jadwal->save();

            return response()->json([
                'status' => true,
                'msg' => 'Check-in berhasil untuk ' . $user->nama
            ]);
        }

        return response()->json([
            'status' => false,
            'msg' => 'Jadwal tidak ditemukan'
        ]);
    }

    public function checkout(Request $request)
    {
        $user = User::where('image', $request->image)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'msg' => 'Wajah tidak dikenali.'
            ]);
        }

        $jadwal = Scheadules::where('date_schedule', Carbon::today()->toDateString())
            ->where('id_user', $user->id)
            ->where('id_shift', $request->shift_id)
            ->first();

        if ($jadwal) {
            $jadwal->check_out = now()->format('H:i:s');
            $jadwal->save();

            return response()->json([
                'status' => true,
                'msg' => 'Check-out berhasil untuk ' . $user->nama
            ]);
        }

        return response()->json([
            'status' => false,
            'msg' => 'Jadwal tidak ditemukan'
        ]);
    }

    public function setKeterangan(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'date_schedule' => 'required|date',
            'id_shift' => 'required|exists:shift,id',
            'keterangan' => 'required|string',
        ]);

        $jadwal = Scheadules::where([
            'id_user' => $request->id_user,
            'date_schedule' => $request->date_schedule,
            'id_shift' => $request->id_shift,
        ])->first();

        if ($jadwal) {
            $jadwal->keterangan = $request->keterangan;
            $jadwal->save();
        }

        return redirect()->back()->with('success', 'Keterangan berhasil diupdate.');
    }
}
