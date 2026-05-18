<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
