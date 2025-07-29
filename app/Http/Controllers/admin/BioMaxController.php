<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BioMaxController extends Controller
{
    public function biomax_status(Request $request){
        return $request->all();
    }
}
