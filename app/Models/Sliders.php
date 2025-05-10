<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sliders extends Model
{
    use SoftDeletes;

    protected $table = 'sliders';

	public static $folderPath = "uploads/sliders/";

    protected $fillable = ['image', 'video_url', 'media_type', 'slider_type', 'redirect_url', 'status', 'created_by', 'updated_by', 'deleted_by'];

    public $appends = ['image_url'];

    public static $slider_type = [
        'application_dashboard_top' => 'Application Dashboard Top',
        'application_dashboard_center' => 'Application Dashboard Center',
        // 'dashboard_bottom' => 'Dashboard Bottom (1920 * 780)',
        // 'dashboard_video' => 'Dashboard Video',
    ];

    public function getSliderTypes($key = null){
        if($key){
            return self::$slider_type[$key];
        }
        return self::$slider_type;
    }

    public function getImageUrlAttribute(){
        # image_url
        if(file_exists(public_path($this->image))){
            return asset($this->image);
        }
        return null;
    }
}
