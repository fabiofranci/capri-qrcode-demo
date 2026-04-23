<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PermitSeeder extends Seeder
{
    public function run(): void
    {
        Permit::truncate();

        Permit::create([
            'plate' => 'AB123CD',
            'holder' => 'Hotel Aurora',
            'type' => 'Navetta',
            'valid_from' => Carbon::now()->subDays(10),
            'valid_to' => Carbon::now()->addDays(20),
            'status' => 'active',
            'qr_token' => Str::uuid(),
        ]);

        Permit::create([
            'plate' => 'EF456GH',
            'holder' => 'NCC Capri Service',
            'type' => 'NCC',
            'valid_from' => Carbon::now()->subDays(30),
            'valid_to' => Carbon::now()->subDays(1),
            'status' => 'active', // ma scaduto
            'qr_token' => Str::uuid(),

        ]);

        Permit::create([
            'plate' => 'IJ789KL',
            'holder' => 'Hotel Vesuvio',
            'type' => 'Navetta',
            'valid_from' => Carbon::now()->subDays(5),
            'valid_to' => Carbon::now()->addDays(30),
            'status' => 'revoked',
            'qr_token' => Str::uuid(),

        ]);
    }
}