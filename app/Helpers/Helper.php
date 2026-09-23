<?php

namespace App\Helpers;

use App\Models\Admission;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\StudentDeviceToken;
use App\Services\FirebaseService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Helper
{
    static function directCan($permission)
    {
        if (!Auth::check()) {
            return false;
        }
        $user = Auth::user();
        $userRole = strtolower(self::getLoginUserRole());
        if (in_array($userRole, ['developer', 'super-admin'])) {
            return true;
        }
        return $user->permissions->pluck('name')->contains($permission) || $user->getAllPermissions()->pluck('name')->contains($permission);
    }

    static function directCanAny(array $permissions)
    {
        if (!Auth::check()) {
            return false;
        }
        $user = Auth::user();
        $userRole = strtolower(self::getLoginUserRole());
        if (in_array($userRole, ['developer', 'super-admin'])) {
            return true;
        }
        return $user->permissions->whereIn('name', $permissions)->isNotEmpty() || $user->getAllPermissions()->whereIn('name', $permissions)->isNotEmpty();
    }

    static function randomToken()
    {
        return sha1(md5(time()) . time() . rand());
    }

    static function makeSlug(string $string)
    {
        return Str::slug(strtolower($string));
    }

    static function generateString($length = 8)
    {
        return Str::random($length);
    }

    static function get_login_user_profile_img($id = null)
    {
        $return_image = asset('admin/assets/images/users/user-dummy-img.jpg');
        if ($id == NULL) {
            $id = Auth::user()->id;
        }

        // $user_image = ProjectImages::where('parent_title','user_image')->where('parent_id',$id)->orderBy('id','DESC')->first();
        // $user_image = ProjectImages::where('parent_title', 'user_image')->where('parent_id', $id)->orderBy('id', 'DESC')->first();
        // $return_image = asset('defaultuser.jpg');
        // if (isset($user_image) && $user_image != '') {
        //     $return_image = $user_image->image;
        //     if (File::exists(env('DIR_PUBLIC_IMAGES') . $return_image)) {
        //         $return_image = env('APP_URL') . $return_image;
        //     }
        // }
        return $return_image;
    }

    static function getAfterAuthRole()
    {
        $roles = new Role();

        $whereNotIn = ["developer"];
        if (strtolower(self::getLoginUserRole()) == "developer") {
            $whereNotIn = [];
        } else if (strtolower(self::getLoginUserRole()) == "super-admin") {
            $whereNotIn[] = "super-admin";
        } else {
            $permissionToiRoles = Permission::where('name', 'system-user-list')->first()?->roles;
            if (count($permissionToiRoles)) {
                $whereNotIn = array_merge($whereNotIn, $permissionToiRoles->pluck('name')->toArray());
                $whereNotIn = array_unique($whereNotIn);
            }
        }

        if (count($whereNotIn) > 0) {
            $roles = $roles->whereNotIn(DB::raw('LOWER(name)'),  $whereNotIn);
        }
        // dd($roles->get()->toArray(), strtolower(self::getLoginUserRole()));
        return $roles = $roles->get();
    }

    static function getRoleName($id)
    {
        if (!in_array($id, ['-1', '0'])) {
            $findRole = Role::where('id', $id)->first();
            if ($findRole && isset($findRole->name)) {
                return $findRole->name;
            }
        }
        return "";
    }

    static function getLoginUserRole()
    {
        $login_user_role = Auth::user()->getRoleNames()->first();
        return ucfirst($login_user_role);
    }

    static function getRolesBasedOnLoginRole()
    {

        $login_user_role = Auth::user()->getRoleNames()->first();
        $roles = new Role();
        if ($login_user_role == "developer") {
            // $roles = $roles->whereNotIn("name", ["developer"]);
        } else if ($login_user_role == "super-admin") {
            $roles = $roles->whereNotIn("name", ["developer", $login_user_role]);
        } else {
            $roles = $roles->whereIn("name", ["other"]);
        }
        return $roles = $roles->get();
    }

    static function getApplicationUserRoles()
    {
        $roles = new Role();
        $roles = $roles->whereIn('name', ["Mechanics", "Other"]);
        return $roles = $roles->get();
    }

    static function convert_date($dateString = "", $current_format = 'd/m/Y', $new_format = 'd/m/Y')
    {
        if ($dateString == "") {
            $dateString = \Carbon\Carbon::now();
            return $dateString->format($new_format);
        }
        /*if($current_format == 'Y-m-d H:i:s'){
            return \Carbon\Carbon::createFromFormat($current_format, $dateString)->format($new_format);
        }*/
        return \Carbon\Carbon::createFromFormat($current_format, $dateString)->format($new_format);
    }

    /** Generate Random String */
    public static function RandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function generateNumericOTP($length = 4)
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    /** Existing image convert to webp */
    static function existingImageConverToWebp($extension, $root_path, $file_name, $upload_file_name, $quality = 100, $imagedestroy = false)
    {
        try {
            $file_path = $root_path . $file_name;
            if (file_exists($file_path)) {
                // dd("L-274", 'exist', $extension, $root_path, $file_name, $upload_file_name, $quality, $imagedestroy, $upload_file_name . '.webp', $root_path.$upload_file_name . '.webp');
                $image = null;
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $getFileMimeType = finfo_file($finfo, $file_path); // image/png | image/jpeg | image/jpg
                finfo_close($finfo);

                if (strtolower($getFileMimeType) == 'image/png') {
                    $image = imagecreatefrompng($file_path);
                    imagepalettetotruecolor($image);
                } else  if (strtolower($getFileMimeType) == "image/jpg" || strtolower($getFileMimeType) == "image/jpeg") {
                    $image = imagecreatefromjpeg($file_path);
                } else  if (strtolower($getFileMimeType) == "image/webp") {
                    $image = imagecreatefromwebp($file_path);
                } else {
                    if (strtolower($extension) == 'png') {
                        $image = imagecreatefrompng($file_path);
                        imagepalettetotruecolor($image);
                    } else  if (strtolower($extension) == "jpg" || strtolower($extension) == "jpeg") {
                        $image = imagecreatefromjpeg($file_path);
                    } else {
                        return '';
                    }
                }
                // return
                $upload_file_name = self::makeSlug($upload_file_name) . '.webp';
                // dd("L-288", 'exist', $extension, $root_path, $dir, $file_name, $upload_file_name, $quality, $imagedestroy, $getFileMimeType, $image, $upload_file_name);

                imagewebp($image, $root_path . $upload_file_name, $quality);

                // If file is not store in proper folder copy file
                // copy(public_path($upload_file_name), $dir.$upload_file_name);

                // dd("L-295", 'exist', $extension, $root_path, $dir, $file_name, $upload_file_name, $quality, $imagedestroy, $root_path.$upload_file_name . '.webp', $getFileMimeType, $image);
                //delete initial uploaded png image
                if ($imagedestroy) {
                    unlink($file_path);
                    // imagedestroy($file_path);
                }
                return $upload_file_name;
            }
            return null;
        } catch (\Exception $e) {
            dd("Helper existingImageConverToWebp NR-131", $e);
            return null;
            dump($e);
        }
        dd("L-135", 'not exist', $root_path, $file_name, $upload_file_name, $imagedestroy);
    }

    /** Check the table exist in the database */
    static function checkTableExist($tableName)
    {
        try {
            return DB::connection()->getSchemaBuilder()->hasTable($tableName);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /** Get the table wise get auto increment id */
    static function getTableWiseGetAutoIncrementId($tableName)
    {
        try {
            if (self::checkTableExist($tableName)) {
                $databaseName = DB::getDatabaseName();

                $autoIncrementInfo = DB::table('information_schema.tables')
                    ->where('table_schema', $databaseName)
                    ->where('table_name', $tableName)
                    ->value('AUTO_INCREMENT');

                if ($autoIncrementInfo) {
                    return $autoIncrementInfo;
                } else {
                    return self::RandomString(4);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /** Calculation Request to Point to Rupees */
    static function calculationPoint2Rupees($available_points)
    {
        try {
            $pointToRupeesConvert = Setting::where('key', 'point-to-rupees')->first();
            $available_points = (int)$available_points;
            $calculation_of_point_2_rupees = 0;
            if ($pointToRupeesConvert) {
                $point = $pointToRupeesConvert?->value?->point ? (int)$pointToRupeesConvert?->value?->point : 0;
                $rupees = $pointToRupeesConvert?->value?->rupees ? (int)$pointToRupeesConvert?->value?->rupees : 0;
                if ($point && $rupees && $available_points) {
                    $calculation_of_point_2_rupees = ($available_points * $rupees) /  $point;
                }
            }
            return (int)$calculation_of_point_2_rupees;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
   public static function sendPushNotification($studentId, $title, $body)
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $messaging = $factory->createMessaging();

        $tokens = StudentDeviceToken::where('student_id', $studentId)->pluck('device_token')->toArray();

        if (count($tokens) > 0) {
            foreach ($tokens as $token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(FcmNotification::create($title, $body));

                $messaging->send($message);
            }
        }
    }

    // <message>otp for application login is ' . $otp . '. Vrundavan Computers - Keshod for info visit - vckguj.com. mo. 9375221111 -HGE</message>
    public static function sendOtpSms($mobileNo, $otp)
    {
        $xml_data = '<?xml version="1.0"?>
            <smslist>
            <sms>
            <user>Vckguj</user>
            <password>592a9ca9f2XX</password>
            <message>otp for application login is ' . $otp . '. Vrundavan Computers - Keshod for info visit - vckguj.com. mo. 9375221111 -HGE</message>  
            <mobiles>' . $mobileNo . '</mobiles>
            <senderid>HGHelp</senderid>
            <tempid>1607100000000273981</tempid>
            </sms>
            </smslist>';

        $URL = "http://vck.mysmsapps.co.in/sendsms.jsp?"; 
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
