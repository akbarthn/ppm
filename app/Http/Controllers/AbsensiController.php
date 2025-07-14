<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheadules;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Auth;

class AbsensiController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $jadwal = Scheadules::with(['user', 'shift'])
        ->where('date_schedule', $today)
        ->orderBy('id_shift')
        ->orderBy('id_user')
        ->get()
        ->groupBy('id_shift');

        return view('absensi.index', compact('jadwal'));
    }

    public function scan($shiftId)
    {
        return view('absensi.scan', compact('shiftId'));
    }

    public function checkin(Request $request)
    {
        $user = User::where('image', $request->image)->first();
        if (!$user) return response()->json(['status' => false, 'msg' => 'Wajah tidak dikenali.']);

        $jadwal = Scheadules::where('date_schedule', Carbon::today()->toDateString())
            ->where('id_user', $user->id)
            ->where('id_shift', $request->shift_id)
            ->first();

        if ($jadwal) {
            $jadwal->check_in = now()->format('H:i:s');
            $jadwal->keterangan = null; // karena hadir
            $jadwal->save();

            return response()->json(['status' => true, 'msg' => 'Check-in berhasil untuk ' . $user->nama]);
        }

        return response()->json(['status' => false, 'msg' => 'Jadwal tidak ditemukan']);
    }

    public function checkout(Request $request)
    {
        $user = User::where('image', $request->image)->first();
        if (!$user) return response()->json(['status' => false, 'msg' => 'Wajah tidak dikenali.']);

        $jadwal = Scheadules::where('date_schedule', Carbon::today()->toDateString())
            ->where('id_user', $user->id)
            ->where('id_shift', $request->shift_id)
            ->first();

        if ($jadwal) {
            $jadwal->check_out = now()->format('H:i:s');
            $jadwal->save();

            return response()->json(['status' => true, 'msg' => 'Check-out berhasil untuk ' . $user->nama]);
        }

        return response()->json(['status' => false, 'msg' => 'Jadwal tidak ditemukan']);
    }

    public function setKeterangan(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'date_schedule' => 'required',
            'id_shift' => 'required',
            'keterangan' => 'required',
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

        return redirect()->back()->with('success', 'Keterangan berhasil diupdate');
    }
}
