<?php
namespace App\Http\Controllers;

use App\Models\Scheadules;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


class ScheadulesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        $grouped = Scheadules::with(['user', 'shift'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nama', 'like', "%$search%");
                })->orWhereHas('shift', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                })->orWhere('date_schedule', 'like', "%$search%");
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->date_schedule . '-' . $item->id_shift;
            });
    
        $jadwal = collect();
    
        foreach ($grouped as $items) {
            $first = $items->first();
            $jadwal->push([
                'date_schedule' => $first->date_schedule,
                'id_shift' => $first->id_shift,
                'shift' => $first->shift,
                'users' => $items->pluck('user.nama')->filter()->toArray(),
            ]);
        }
    
        return view('jadwal.index', compact('jadwal'));
    }
    
    public function create()
    {
        $users = User::where('role', null)->get(); // hanya karyawan
        $shifts = Shift::all(); // misal: shift pagi & sore
        return view('jadwal.create', compact('users', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'jadwal' => 'required|array',
        ]);
    
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = CarbonPeriod::create($start, $end);
    
        foreach ($days as $date) {
            $dayName = strtolower($date->format('l')); // monday, tuesday, etc.
    
            if (isset($request->jadwal[$dayName])) {
                $shiftId = $request->jadwal[$dayName]['shift'] ?? null;
    
                // Pastikan shift diisi dan users-nya tersedia (tidak error)
                if ($shiftId && isset($request->jadwal[$dayName]['users']) && is_array($request->jadwal[$dayName]['users'])) {
                    $users = $request->jadwal[$dayName]['users'];
    
                    foreach ($users as $userId) {
                        Scheadules::create([
                            'date_schedule' => $date->toDateString(),
                            'id_shift' => $shiftId,
                            'id_user' => $userId,
                        ]);
                    }
                }
            }
        }
    
        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dibuat.');
    }

    public function destroyGroup(Request $request)
    {
        Scheadules::where('date_schedule', $request->date_schedule)
                ->where('id_shift', $request->id_shift)
                ->delete();

        return redirect()->route('jadwal.index')->with('success', 'Semua jadwal dalam grup berhasil dihapus.');
    }

    public function editGroup(Request $request)
{
    $date = $request->query('date_schedule');
    $shiftId = $request->query('id_shift');

    $jadwal = Scheadules::where('date_schedule', $date)
        ->where('id_shift', $shiftId)
        ->with('user', 'shift')
        ->get();

    $users = User::where('role', null)->get(); // hanya karyawan
    $shifts = Shift::all();

    return view('jadwal.edit-group', compact('jadwal', 'users', 'shifts', 'date', 'shiftId'));
}

public function updateGroup(Request $request)
{
    $request->validate([
        'date_schedule' => 'required|date',
        'id_shift' => 'required|exists:shift,id',
        'users' => 'nullable|array',
    ]);

    $date = $request->date_schedule;
    $shiftId = $request->id_shift;
    $selectedUsers = array_filter($request->users ?? [], fn($id) => !is_null($id) && $id !== '');

    // Ambil user lama dari jadwal
    $existingUsers = Scheadules::where('date_schedule', $date)
        ->where('id_shift', $shiftId)
        ->pluck('id_user')
        ->toArray();

    // Tambahkan yang baru (yang belum ada)
    foreach ($selectedUsers as $userId) {
        if (!in_array($userId, $existingUsers)) {
            Scheadules::create([
                'date_schedule' => $date,
                'id_shift' => $shiftId,
                'id_user' => $userId,
            ]);
        }
    }

    // Hapus hanya user yang sebelumnya ada tapi sekarang tidak dipilih lagi
    foreach ($existingUsers as $userId) {
        if (!in_array($userId, $selectedUsers)) {
            Scheadules::where('date_schedule', $date)
                ->where('id_shift', $shiftId)
                ->where('id_user', $userId)
                ->delete();
        }
    }

    return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
}


}
