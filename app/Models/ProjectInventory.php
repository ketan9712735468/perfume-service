<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectInventory extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'file', 'original_name'];

    protected $appends = ['file_url'];

    public static string $FOLDER_PATH = 'uploads/project_inventories';
    public static int $FILE_SIZE = 2; // MB

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file) {
            return Storage::url(self::$FOLDER_PATH . '/' . $this->file);
        }

        return null;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

