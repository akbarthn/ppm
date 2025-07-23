<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheadules;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    // Konstanta untuk waktu check-in/out
    private const CHECKIN_EARLY_MINUTES = 15;
    private const CHECKIN_LATE_MINUTES = 30;
    private const CHECKOUT_EARLY_MINUTES = 30;
    private const CHECKOUT_LATE_MINUTES = 60;

public function index()
{
    $today = Carbon::today()->toDateString();
    $userId = auth()->id();

    $shifts = Shift::all();
    $jadwal = [];

    foreach ($shifts as $shift) {
        $items = Scheadules::with('user')
            ->where('id_shift', $shift->id) // SESUAIKAN INI DENGAN KOLOM YANG BENAR
            ->whereDate('date_schedule', $today)
            ->get();

        if ($items->count() > 0) {
            $jadwal[] = [
                'shift' => $shift,
                'items' => $items
            ];
        }
    }

    return view('absensi.index', compact('jadwal'));
}

    public function scan()
    {
        return view('absensi.scan'); // Pastikan ada file resources/views/absensi/scan.blade.php
    }


    public function checkStatus(Request $request)
{
    try {
        $user = User::find($request->id); // <-- Ubah dari where('nama') menjadi find(id)
        if (!$user) return $this->respondFail('User tidak ditemukan', 404);

        $today = Carbon::today();
        $now = Carbon::now();

        $jadwals = Scheadules::with('shift')
            ->whereDate('date_schedule', $today)
            ->where('id_user', $user->id)
            ->get();

        if ($jadwals->isEmpty()) return $this->respondFail('Tidak ada jadwal untuk hari ini.');

        $matchedShift = null;
        $bestScore = -1;

        foreach ($jadwals as $jadwal) {
            $shift = $jadwal->shift;

            if (!$shift) continue;

            $shiftStart = Carbon::createFromFormat('H:i:s', $shift->start_time);
            $shiftEnd = Carbon::createFromFormat('H:i:s', $shift->end_time);

            $checkinStart = $shiftStart->copy()->subMinutes(self::CHECKIN_EARLY_MINUTES);
            $checkinEnd = $shiftStart->copy()->addMinutes(self::CHECKIN_LATE_MINUTES);
            $checkoutStart = $shiftEnd->copy()->subMinutes(self::CHECKOUT_EARLY_MINUTES);
            $checkoutEnd = $shiftEnd->copy()->addMinutes(self::CHECKOUT_LATE_MINUTES);

            $score = 0;
            $status = null;

            if ($now->between($checkinStart, $checkinEnd)) {
                $score = 2;
                $status = 'checkin';
            } elseif ($now->between($checkoutStart, $checkoutEnd)) {
                $score = 1;
                $status = 'checkout';
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $matchedShift = [
                    'jadwal_id' => $jadwal->id,
                    'shift_id' => $shift->id,
                    'shift_name' => $shift->shift_name,
                    'shift_start' => $shift->start_time,
                    'shift_end' => $shift->end_time,
                    'aksi' => $status,
                    'keterangan' => $status == 'checkin' ? 'Absen Masuk' : 'Absen Pulang',
                    'nama' => $user->nama
                ];
            }
        }

        if ($matchedShift) {
            return $this->respondSuccess('Jadwal ditemukan', $matchedShift);
        }

        return $this->respondFail('Tidak ada jadwal yang cocok untuk waktu saat ini.');
    } catch (\Exception $e) {
        Log::error('CheckStatus Error: ' . $e->getMessage());
        return $this->respondFail('Terjadi kesalahan pada server.', 500);
    }
}


    public function checkin(Request $request)
    {
        try {
            $jadwal = Scheadules::whereDate('date_schedule', Carbon::today())
                ->where('id_user', $request->id)
                ->where('id_shift', $request->shift_id)
                ->first();

            if (!$jadwal) return $this->respondFail('Jadwal tidak ditemukan.');

            if ($jadwal->check_in) {
                return $this->respondFail('Anda sudah melakukan check-in sebelumnya.');
            }

            $jadwal->check_in = Carbon::now();
            $jadwal->keterangan = 'Absen Masuk';
            $jadwal->save();

            return $this->respondSuccess('Check-in berhasil.');

        } catch (\Exception $e) {
            Log::error('Check-in Error: ' . $e->getMessage());
            return $this->respondFail('Gagal melakukan check-in.', 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            $jadwal = Scheadules::whereDate('date_schedule', Carbon::today())
                ->where('id_user', $request->id)
                ->where('id_shift', $request->shift_id)
                ->first();

            if (!$jadwal) return $this->respondFail('Jadwal tidak ditemukan.');

            if (!$jadwal->check_in) {
                return $this->respondFail('Anda belum melakukan check-in.');
            }

            if ($jadwal->check_out) {
                return $this->respondFail('Anda sudah melakukan check-out sebelumnya.');
            }

            $jadwal->check_out = Carbon::now();
            $jadwal->save();

            return $this->respondSuccess('Check-out berhasil.');

        } catch (\Exception $e) {
            Log::error('Check-out Error: ' . $e->getMessage());
            return $this->respondFail('Gagal melakukan check-out.', 500);
        }
    }

    // ========== HELPER RESPONSE JSON ==========
    private function respondSuccess(string $message, array $data = [])
    {
        return response()->json([
            'status' => true,
            'msg' => $message,
            'data' => $data
        ]);
    }

    private function respondFail(string $message, int $code = 400)
    {
        return response()->json([
            'status' => false,
            'msg' => $message
        ], $code);
    }
}
