<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redemption extends Model
{
    use SoftDeletes;

    protected $table = 'redemptions';

    protected $fillable = [
        'label',
        'redemption_type',
        'quantity',
        'collect_points',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function redemption_items()
    {
        return $this->hasMany(RedemptionItem::class, 'redemption_id', 'id');
    }
}
