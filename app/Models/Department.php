<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $primaryKey = 'dept_id';

    protected $fillable = [
        'dept_name', 'faculty'
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'dept_id');
    }
}
