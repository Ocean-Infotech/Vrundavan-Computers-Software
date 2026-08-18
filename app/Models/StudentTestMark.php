<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTestMark extends Model
{
    use HasFactory;

    protected $table = 'student_test_marks';

    protected $fillable = [
        'test_id',
        'register_id',
        'marks',
        'created_by',
        'updated_by',
    ];


    // Each mark belongs to a test
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    // Each mark belongs to a student (Admission model)
    public function student()
    {
        return $this->belongsTo(Admission::class, 'register_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
