<?php

namespace App\Models;

use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterState;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
	public $table = 'user_details';

	protected $appends = ['country_name', 'state_name', 'city_name'];

	protected $fillable = [
		'user_id',
		'applied_referral_code',
		'gender',
		'date_of_birth',
		'country_id',
		'state_id',
		'city_id',
		'pincode',
	];

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

	public function getStateNameAttribute()
	{
		# state_name
		if ($this->state_id) {
			$getState = MasterState::find($this->state_id);
			if ($getState && isset($getState->name)) {
				return $getState->name;
			}
		}
		return "";
	}

	public function getCityNameAttribute()
	{
		# city_name
		if ($this->city_id) {
			$getCity = MasterCity::find($this->city_id);
			if ($getCity && isset($getCity->name)) {
				return $getCity->name;
			}
		}
		return "";
	}
}
