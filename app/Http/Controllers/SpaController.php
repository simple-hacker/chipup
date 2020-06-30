<?php

namespace App\Http\Controllers;

use App\Attributes\Limit;
use App\Attributes\Variant;
use App\Attributes\TableSize;

class SpaController extends Controller
{
    public function index()
    {
        $data = [
            'user' => auth()->user(),
            'stakes' => auth()->user()->stakes,
            'limits' => Limit::all(),
            'variants' => Variant::all(),
            'table_sizes' => TableSize::all(),
        ];

        return view('spa', $data);
    }
}
