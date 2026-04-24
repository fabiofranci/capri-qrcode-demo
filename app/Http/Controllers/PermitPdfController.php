<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class PermitPdfController extends Controller
{
    public function download(Permit $permit)
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data(url('/verify/' . $permit->qr_token))
            ->size(300)
            ->margin(1)
            ->build();

        $qr = base64_encode($result->getString());

        $pdf = Pdf::loadView('pdf.permit_badge', [
            'permit' => $permit,
            'qr' => $qr,
        ])->setPaper('A6', 'portrait');

        return $pdf->stream();
    }
}




