<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function getIndex(Request $request)
    {
        $data = $request->session()->get('data');

        return view('info')->with('data', $data);
    }
}
