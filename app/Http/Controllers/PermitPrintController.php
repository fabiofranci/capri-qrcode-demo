<?php

namespace App\Http\Controllers;

use App\Models\Permit;

class PermitPrintController extends Controller
{
    public function show($id)
    {
        $permit = Permit::findOrFail($id);
        return view('print', compact('permit'));
    }

    public function all()
    {
        $permits = Permit::all();
        return view('print-all', compact('permits'));
    }
}