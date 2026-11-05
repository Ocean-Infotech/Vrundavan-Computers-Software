<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HscMarksheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'registration_id',
        'exam_title',
        'academic_year',
        'standard',
        'roll_number',
        'is_absent',
        'marks',
        'total_obtained',
        'total_maximum',
        'percentage',
        'grade',
        'rank',
        'result',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'marks' => 'array',
        'is_absent' => 'boolean',
        'total_obtained' => 'float',
        'total_maximum' => 'float',
        'percentage' => 'float',
    ];

    public static function subjects(): array
    {
        return [
            ['key' => 'english', 'name' => 'English', 'max' => 50],
            ['key' => 'economics', 'name' => 'Economics', 'max' => 50],
            ['key' => 'ocm', 'name' => 'Organization of Commerce and Management', 'max' => 50],
            ['key' => 'accountancy', 'name' => 'Accountancy', 'max' => 50],
            ['key' => 'gujarati', 'name' => 'Gujarati', 'max' => 50],
            ['key' => 'spcc', 'name' => 'Secretarial Practice & Commercial Correspondence (SPCC)', 'max' => 50],
            ['key' => 'statistics', 'name' => 'Statistics', 'max' => 50],
            ['key' => 'computer', 'name' => 'Computer', 'max' => 50],
        ];
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'student_id', 'id');
    }

    public function registration()
    {
        return $this->belongsTo(CourceRegistration::class, 'registration_id', 'id');
    }

    public function studentName(): string
    {
        $a = $this->admission;
        if (!$a) {
            return '-';
        }

        return trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '') . ' ' . ($a->father_name ?? ''));
    }
}
