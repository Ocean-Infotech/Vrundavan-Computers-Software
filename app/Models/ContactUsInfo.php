<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUsInfo extends Model
{
    protected $table = 'contact_us_infos';

    protected $fillable = [
        'group_title',
        'college_mobile',
        'school_number',
        'phone',
        'distance',
        'principal',
        'created_by',
        'updated_by',
    ];

    public static function current(): self
    {
        $info = static::query()->first();
        if ($info) {
            return $info;
        }

        return static::query()->create([
            'group_title' => 'Vrundavan Computer',
            'college_mobile' => '',
            'school_number' => '',
            'phone' => '',
            'distance' => '',
            'principal' => '',
        ]);
    }
}
