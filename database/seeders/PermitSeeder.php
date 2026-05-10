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

        // === STRUTTURE (ragione sociale nel nome, cognome null) ===
        $hotelAurora = PermitHolder::firstOrCreate([
            'nome' => 'Hotel Aurora',
            'cognome' => null,
        ]);

        $capriService = PermitHolder::firstOrCreate([
            'nome' => 'Capri Service NCC',
            'cognome' => null,
        ]);

        $hotelVesuvio = PermitHolder::firstOrCreate([
            'nome' => 'Hotel Vesuvio',
            'cognome' => null,
        ]);

        // === PRIVATI (cognome e nome separati) ===
        $guideMario = PermitHolder::firstOrCreate([
            'nome' => 'Mario',
            'cognome' => 'Rossi',
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

        $vehicleGuide = Vehicle::firstOrCreate([
            'targa' => 'MN111OP',
        ], [
            'permit_holder_id' => $guideMario->id,
            'marca' => 'Volkswagen',
            'modello' => 'Golf',
            'colore' => 'Blu',
        ]);

        // === PERMITS STRUTTURE ===
        Permit::create([
            'permit_holder_id' => $hotelAurora->id,
            'vehicle_id' => $vehicleAurora->id,
            'holder' => $hotelAurora->nome,
            'plate' => $vehicleAurora->targa,
            'type' => 'navetta',
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
            'type' => 'navetta',
            'valid_from' => Carbon::now()->subDays(5),
            'valid_to' => Carbon::now()->addDays(30),
            'status' => 'revoked',
            'qr_token' => Str::uuid(),
        ]);

        // === PERMITS PRIVATI ===
        Permit::create([
            'permit_holder_id' => $guideMario->id,
            'vehicle_id' => $vehicleGuide->id,
            'holder' => trim($guideMario->cognome . ' ' . $guideMario->nome),
            'plate' => $vehicleGuide->targa,
            'type' => 'NCC',
            'valid_from' => Carbon::now()->subDays(15),
            'valid_to' => Carbon::now()->addDays(45),
            'status' => 'active',
            'qr_token' => Str::uuid(),
        ]);
    }
}