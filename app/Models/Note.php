<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    protected $fillable = [
        'subject',
        'note',
        'attachments',
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
