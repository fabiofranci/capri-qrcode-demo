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
                'reason' => 'Permesso non trovato'
            ]);
        }

        if ($permit->status === 'revoked') {
            return view('verify', [
                'status' => 'invalid',
                'reason' => 'Permesso revocato',
                'permit' => $permit
            ]);
        }

        if (Carbon::now()->gt($permit->valid_to)) {
            return view('verify', [
                'status' => 'invalid',
                'reason' => 'Permesso scaduto',
                'permit' => $permit
            ]);
        }

        return view('verify', [
            'status' => 'valid',
            'permit' => $permit
        ]);
    }
}