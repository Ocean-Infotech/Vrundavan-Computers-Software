<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterState extends Model
{
	use HasFactory, SoftDeletes;

	public $table = 'master_states';

	protected $appends = ['country_name'];

	protected $fillable = [
		'country_id',
		'name',
		'short_name',
		'gst_code',
		'latitude',
		'longitude',
		'status',
		'created_by',
		'updated_by',
		'deleted_by',
	];

	public function country()
	{
		return $this->belongsTo(MasterCountry::class, 'country_id');
	}

	public function getCountryNameAttribute()
	{
		# country_name
		if ($this->country_id) {
			$getCountry = MasterCountry::find($this->country_id);
			if ($getCountry && isset($getCountry->name)) {
				return $getCountry->name;
			}
		}
		return "";
	}
}
