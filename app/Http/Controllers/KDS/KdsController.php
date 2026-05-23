<?php

namespace App\Http\Controllers\KDS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function index()
    {
        return view('kds.index');
    }
}
