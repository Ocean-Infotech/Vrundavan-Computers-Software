<?php

namespace App\Models;

use App\Models\Master\MasterBatch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    public $table = 'timetables';

    protected $fillable = [
        'class',
        'semester',
        'batch',
        'attachment',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
     public function batch()
    {
        return $this->belongsTo(MasterBatch::class, 'batch_id');
    }
}
