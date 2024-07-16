<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }
    
    public function resultFiles()
    {
        return $this->hasMany(ResultFile::class);
    }

    public function inventories()
    {
        return $this->hasMany(ProjectInventory::class);
    }
}
