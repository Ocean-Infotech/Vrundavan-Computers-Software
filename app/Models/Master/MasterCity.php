<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCity extends Model
{
    use SoftDeletes;

	public $table = 'master_cities';

	protected $appends = ['country_name','state_name'];

	protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'short_name',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getCountryNameAttribute(){
		# country_name
		if($this->country_id){
			$getCountry = MasterCountry::find($this->country_id);
			if($getCountry && isset($getCountry->name)){
				return $getCountry->name;
			}
		}
		return "";
	}

	public function getStateNameAttribute(){
		# state_name
		if($this->state_id){
			$getState = MasterState::find($this->state_id);
			if($getState && isset($getState->name)){
				return $getState->name;
			}
		}
		return "";
	}
}
