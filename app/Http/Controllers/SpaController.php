<?php

namespace App\Http\Controllers;

use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use Illuminate\Http\Request;
use App\Attributes\TableSize;

class SpaController extends Controller
{
    public function index()
    {
        $data = [
            'user' => auth()->user(),
            'stakes' => Stake::all(),
            'limits' => Limit::all(),
            'variants' => Variant::all(),
            'table_sizes' => TableSize::all(),
        ];

        return view('spa', $data);
    }
}
