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
                'reason' => 'not_found',
            ], 404);
        }

        $result = $permit->getValidationResult();

        return response()->json([
            ...$result,
            'holder_name' => $permit->holder_name,
            'holder' => $permit->holder,
            'plate' => $permit->plate,
            'type' => $permit->type,
            'valid_to' => optional($permit->valid_to)->format('Y-m-d'),
        ]);
    }
}
