<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyPageController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Permit;

Route::get('/qr-test', function () {
    $permit = Permit::first();

    $url = url('/verify/' . $permit->qr_token);

    return QrCode::size(300)->generate($url);
});

Route::get('/verify/{token}', [VerifyPageController::class, 'show']);

Route::get('/', function () {
    return view('welcome');
});
