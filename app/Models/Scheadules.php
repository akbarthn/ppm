<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;


class Scheadules extends Model
{
    
    use HasFactory;

    protected $table = 'scheadules';

    protected $fillable = [
        'date_schedule',
        'id_shift',
        'id_user',
        'check_in',
        'check_out',
    ];
    // Casting atribut ke tipe data tertentu
    protected $casts = [
        'date_schedule' => 'date', // Otomatis menjadi objek Carbon
        'check_in' => 'datetime',  // Otomatis menjadi objek Carbon
        'check_out' => 'datetime', // Otomatis menjadi objek Carbon
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function shift() {
        return $this->belongsTo(Shift::class, 'id_shift');
    }
}
