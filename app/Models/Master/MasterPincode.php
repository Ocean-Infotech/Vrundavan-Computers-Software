<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MasterPincode extends Model
{
    use SoftDeletes;

    public $table = 'master_pincodes';

	protected $appends = ['country_name','state_name', 'city_name'];

    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'pincode',
        'search_name',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pincode) {
            $pincode->search_name = $pincode->searchName();
        });

        static::updating(function ($pincode) {
            $pincode->search_name = $pincode->searchName();
        });
    }

    public function searchName()
    {
        $search_name = array_filter([
            $this->getCityNameAttribute(),
            $this->getStateNameAttribute(),
            $this->getCountryNameAttribute(),
        ]);
        return implode(', ', $search_name);
    }
    // Define relationships
    public function country()
    {
        return $this->belongsTo(MasterCountry::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(MasterState::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }

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

	public function getCityNameAttribute(){
		# city_name
		if($this->city_id){
			$getCity = MasterCity::find($this->city_id);
			if($getCity && isset($getCity->name)){
				return $getCity->name;
			}
		}
		return "";
	}
}
