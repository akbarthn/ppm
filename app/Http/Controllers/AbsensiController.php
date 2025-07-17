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
            $items = $jadwals->get($shift->id, collect());

    // Tambahkan logika aksi per item
    $items = $items->map(function ($item) {
        if (!$item->check_in) {
            $item->aksi = 'checkin';
        } elseif ($item->check_in && !$item->check_out) {
            $item->aksi = 'checkout';
        } else {
            $item->aksi = 'done'; // Sudah check in dan check out
        }
        return $item;
    });

    $data->push([
        'shift' => $shift,
        'items' => $items,
    ]);
        }

        return view('absensi.index', [
            'jadwal' => $data,
            'today' => $today,
        ]);

        return view('absensi.index', compact('shifts', 'jadwals', 'aksi'));
    }

    public function scan($shift_id, $aksi)
    {
        return view('absensi.scan', [
            'id_shift' => $shift_id,
            'aksi' => $aksi,
        ]);
    }


    public function checkin(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'nama' => 'required|string',
                'shift_id' => 'required|integer',
            ]);
    
            // Cek user berdasarkan nama
            $user = User::where('nama', $request->nama)->first();
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => '❌ Wajah tidak dikenali. User tidak ditemukan.'
                ], 404);
            }
    
            // Cek jadwal hari ini dan shift
            $jadwal = Scheadules::where('date_schedule', Carbon::today()->toDateString())
                ->where('id_user', $user->id)
                ->where('id_shift', $request->shift_id)
                ->first();
    
            if (!$jadwal) {
                return response()->json([
                    'status' => false,
                    'msg' => '❌ Jadwal tidak ditemukan untuk shift ini.'
                ], 404);
            }
    
            // Simpan check-in
            $jadwal->check_in = now();
            $jadwal->keterangan = null;
            $jadwal->save();
    
            return response()->json([
                'status' => true,
                'msg' => '✅ Check-in berhasil untuk ' . $user->nama
            ]);
        } catch (\Exception $e) {
            // Log error jika perlu
            \Log::error('Check-in Error: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'msg' => '⚠️ Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkout(Request $request)
{
    // logika mirip, tapi mengisi kolom `check_out`
    $user = User::where('nama', $request->nama)->first();
    if (!$user) {
        return response()->json(['status' => false, 'msg' => 'Wajah tidak dikenali.']);
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

    return response()->json(['status' => false, 'msg' => 'Jadwal tidak ditemukan']);
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
