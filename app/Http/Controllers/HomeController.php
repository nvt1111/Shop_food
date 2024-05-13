<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index(){
        $topBanner = Banner::getBanner()->first();
        $gallerys = Banner::getBanner('gallery')->get();
        return view('home.index', compact('topBanner', 'gallerys'));
    }

    public function about(){
        return view('home.about');
    }

    
}
