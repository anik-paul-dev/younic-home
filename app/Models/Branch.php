<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
