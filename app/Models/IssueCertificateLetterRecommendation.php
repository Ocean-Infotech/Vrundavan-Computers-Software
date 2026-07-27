<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueCertificateLetterRecommendation extends Model
{
    protected $table = 'issue_certificate_letter_recommendations';

    protected $fillable = [
        'admission_id',
        'student_name',
        'recommender_type',
        'faculty_id',
        'recommender_name',
        'recommender_designation',
        'issue_date',
        'created_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
