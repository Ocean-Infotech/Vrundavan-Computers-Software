<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    public $table = 'tasks';

    protected $fillable = [
        'date',
        'title',
        'due_date',
        'assigned_to',
        'status',
        'remarks',
        'response',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
