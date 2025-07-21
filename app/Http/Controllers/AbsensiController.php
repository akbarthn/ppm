<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Scheadules;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
        // Baris ini dihapus karena duplikat: return view('absensi.index', compact('shifts', 'jadwals', 'aksi'));
    }

    public function scan()
    {
        return view('absensi.scan');
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
        ]);

        $user = User::where('nama', $request->nama)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'msg' => '❌ Wajah tidak dikenali atau user tidak terdaftar.'
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // Ambil semua jadwal user untuk hari ini
        $jadwals = Scheadules::with('shift')
            ->where('id_user', $user->id)
            ->where('date_schedule', $today)
            ->get();

        if ($jadwals->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => "❌ Tidak ada jadwal absensi untuk {$user->nama} hari ini."
            ]);
        }

        $foundRelevantShift = false;
        $responseDetails = [];

        foreach ($jadwals as $jadwal) {
            $shift = $jadwal->shift;
            if (!$shift) {
                Log::warning("Shift tidak ditemukan untuk jadwal ID: {$jadwal->id}");
                continue;
            }

            $shiftStart = Carbon::parse($shift->jam_masuk);
            $shiftEnd = Carbon::parse($shift->jam_keluar);

            // Rentang waktu untuk Check-in: 10 menit sebelum jam masuk sampai 30 menit sebelum jam keluar
            $checkinStartTime = $shiftStart->copy()->subMinutes(10);
            $checkinEndTime = $shiftEnd->copy()->subMinutes(30);

            // Rentang waktu untuk Check-out: 30 menit sebelum jam keluar sampai 30 menit setelah jam keluar
            $checkoutStartTime = $shiftEnd->copy()->subMinutes(30);
            $checkoutEndTime = $shiftEnd->copy()->addMinutes(30);

            $aksi = null;
            $keterangan = '';

            // Cek kondisi Check-in
            if (is_null($jadwal->check_in) && $now->between($checkinStartTime, $checkinEndTime)) {
                $aksi = 'checkin';
                $keteranganAlert = 'Absen Masuk';
            }
            // Cek kondisi Check-out
            elseif (!is_null($jadwal->check_in) && is_null($jadwal->check_out) && $now->between($checkoutStartTime, $checkoutEndTime)) {
                $aksi = 'checkout';
                $keteranganAlert = 'Absen Pulang';
            } elseif (!is_null($jadwal->check_in) && !is_null($jadwal->check_out)) {
                $aksi = 'done'; // Sudah absen masuk dan pulang
                $keterangan = 'Sudah absen masuk dan pulang';
            }

            if ($aksi && $aksi !== 'done') {
                $foundRelevantShift = true;
                $responseDetails = [
                    'nama' => $user->nama,
                    'shift_id' => $shift->id,
                    'shift_name' => $shift->nama_shift,
                    'shift_start' => $shiftStart->format('H:i'),
                    'shift_end' => $shiftEnd->format('H:i'),
                    'aksi' => $aksi,
                    'keterangan' => $keterangan
                ];
                break; // Ambil shift pertama yang relevan saja
            }
        }

        if ($foundRelevantShift) {
            return response()->json([
                'status' => true,
                'msg' => 'Jadwal dan aksi ditemukan.',
                'data' => $responseDetails
            ]);
        } else {
            // Jika semua jadwal sudah selesai atau tidak ada rentang waktu yang relevan
            return response()->json([
                'status' => false,
                'msg' => 'Tidak ada jadwal yang relevan untuk absensi saat ini atau sudah absen.'
            ]);
        }
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
                    'msg' => '❌ User tidak ditemukan. Wajah mungkin tidak terdaftar dengan benar.'
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

            if (!is_null($jadwal->check_in)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Anda sudah absen masuk untuk shift ini.'
                ], 409); // 409 Conflict cocok untuk status sudah ada
            }

            // Simpan check-in
            $jadwal->check_in = now();
            // Keterangan bisa diatur di sini jika ada logika spesifik, atau di checkStatus
            // $jadwal->keterangan = 'Masuk Tepat Waktu'; // Contoh
            $jadwal->save();

            return response()->json([
                'status' => true,
                'msg' => '✅ Check-in berhasil untuk ' . $user->nama
            ]);
        } catch (\Exception $e) {
            // Log error jika perlu
            Log::error('Check-in Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'msg' => '⚠️ Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }


    public function checkout(Request $request)
    {
        try {
            Log::info('Checkout request:', $request->all());

            $request->validate([
                'nama' => 'required|string',
                'shift_id' => 'required|integer'
            ]);

            $user = User::where('nama', $request->nama)->first();

            if (!$user) {
                Log::warning('User tidak ditemukan: ' . $request->nama);
                return response()->json(['status' => false, 'msg' => 'Wajah tidak dikenali atau user tidak terdaftar.']);
            }

            $today = Carbon::today()->toDateString();

            $jadwal = Scheadules::where('date_schedule', $today)
                ->where('id_user', $user->id)
                ->where('id_shift', $request->shift_id)
                ->first();

            if (!$jadwal) {
                Log::warning("Jadwal tidak ditemukan untuk user {$user->id} dan shift {$request->shift_id} di tanggal {$today}");
                return response()->json(['status' => false, 'msg' => 'Jadwal tidak ditemukan.']);
            }

            if (is_null($jadwal->check_in)) {
                return response()->json(['status' => false, 'msg' => 'Anda belum absen masuk untuk shift ini.']);
            }

            if (!is_null($jadwal->check_out)) {
                return response()->json(['status' => false, 'msg' => 'Sudah check-out sebelumnya.']);
            }

            $jadwal->check_out = now();
            // Keterangan bisa diatur di sini jika ada logika spesifik, atau di checkStatus
            // $jadwal->keterangan = 'Pulang Tepat Waktu'; // Contoh
            $jadwal->save();

            return response()->json([
                'status' => true,
                'msg' => 'Check-out berhasil untuk ' . $user->nama
            ]);
        } catch (\Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'msg' => 'Terjadi kesalahan di server.'
            ], 500);
        }
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
