<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shift';

    protected $fillable = [
        'name', 'start', 'end'
    ];

    /**
     * Hitung durasi shift dalam menit.
     */
    public function getDurationInMinutesAttribute()
    {
        $start = Carbon::createFromFormat('H:i:s', $this->start);
        $end = Carbon::createFromFormat('H:i:s', $this->end);

        // Jika shift berakhir keesokan hari
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $end->diffInMinutes($start);
    }

    /**
     * Format waktu mulai dan selesai jadi H:i (untuk ditampilkan)
     */
    public function getStartFormattedAttribute()
    {
        return Carbon::createFromFormat('H:i:s', $this->start)->format('H:i');
    }

    public function getEndFormattedAttribute()
    {
        return Carbon::createFromFormat('H:i:s', $this->end)->format('H:i');
    }
}
