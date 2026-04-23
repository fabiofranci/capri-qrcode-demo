<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerifyPermitController extends Controller
{
    public function verify($token)
    {
        $permit = Permit::where('qr_token', $token)->first();

        if (!$permit) {
            return response()->json([
                'status' => 'invalid',
                'reason' => 'not_found'
            ]);
        }

        if ($permit->status === 'revoked') {
            return response()->json([
                'status' => 'invalid',
                'reason' => 'revoked'
            ]);
        }

        if (Carbon::now()->gt($permit->valid_to)) {
            return response()->json([
                'status' => 'invalid',
                'reason' => 'expired'
            ]);
        }

        return response()->json([
            'status' => 'valid',
            'plate' => $permit->plate,
            'holder' => $permit->holder,
            'type' => $permit->type,
            'valid_to' => $permit->valid_to->format('Y-m-d'),
        ]);
    }
}