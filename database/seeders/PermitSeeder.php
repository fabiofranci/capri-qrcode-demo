<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permit;
use App\Models\PermitHolder;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PermitSeeder extends Seeder
{
    public function run(): void
    {
        Permit::truncate();

        $hotelAurora = PermitHolder::firstOrCreate([
            'nome' => 'Hotel Aurora',
        ]);

        $capriService = PermitHolder::firstOrCreate([
            'nome' => 'NCC Capri Service',
        ]);

        $hotelVesuvio = PermitHolder::firstOrCreate([
            'nome' => 'Hotel Vesuvio',
        ]);

        $vehicleAurora = Vehicle::firstOrCreate([
            'targa' => 'AB123CD',
        ], [
            'permit_holder_id' => $hotelAurora->id,
            'marca' => 'Fiat',
            'modello' => 'Tipo',
            'colore' => 'Bianco',
        ]);

        $vehicleService = Vehicle::firstOrCreate([
            'targa' => 'EF456GH',
        ], [
            'permit_holder_id' => $capriService->id,
            'marca' => 'Mercedes',
            'modello' => 'Vito',
            'colore' => 'Nero',
        ]);

        $vehicleVesuvio = Vehicle::firstOrCreate([
            'targa' => 'IJ789KL',
        ], [
            'permit_holder_id' => $hotelVesuvio->id,
            'marca' => 'Peugeot',
            'modello' => 'Traveller',
            'colore' => 'Grigio',
        ]);

        Permit::create([
            'permit_holder_id' => $hotelAurora->id,
            'vehicle_id' => $vehicleAurora->id,
            'holder' => $hotelAurora->nome,
            'plate' => $vehicleAurora->targa,
            'type' => 'Navetta',
            'valid_from' => Carbon::now()->subDays(10),
            'valid_to' => Carbon::now()->addDays(20),
            'status' => 'active',
            'qr_token' => Str::uuid(),
        ]);

        Permit::create([
            'permit_holder_id' => $capriService->id,
            'vehicle_id' => $vehicleService->id,
            'holder' => $capriService->nome,
            'plate' => $vehicleService->targa,
            'type' => 'NCC',
            'valid_from' => Carbon::now()->subDays(30),
            'valid_to' => Carbon::now()->subDays(1),
            'status' => 'active', // ma scaduto
            'qr_token' => Str::uuid(),
        ]);

        Permit::create([
            'permit_holder_id' => $hotelVesuvio->id,
            'vehicle_id' => $vehicleVesuvio->id,
            'holder' => $hotelVesuvio->nome,
            'plate' => $vehicleVesuvio->targa,
            'type' => 'Navetta',
            'valid_from' => Carbon::now()->subDays(5),
            'valid_to' => Carbon::now()->addDays(30),
            'status' => 'revoked',
            'qr_token' => Str::uuid(),
        ]);
    }
}