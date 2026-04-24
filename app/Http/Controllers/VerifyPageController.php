<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Carbon\Carbon;

class VerifyPageController extends Controller
{

    public function show($token)
    {
        $permit = Permit::where('qr_token', $token)->first();

        if (!$permit) {
            return view('verify', [
                'status' => 'invalid',
                'reason' => 'Permesso non trovato',
            ]);
        }

        $result = $permit->getValidationResult();

        return view('verify', [
            'status' => $result['status'],
            'reason' => $permit->getReasonLabel(),
            'permit' => $permit,
        ]);
    }
}