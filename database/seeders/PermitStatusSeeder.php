<?php


namespace Database\Seeders;

use App\Models\PermitStatus;
use Illuminate\Database\Seeder;

class PermitStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'active', 'color' => 'success', 'sort' => 1],
            ['name' => 'revoked', 'color' => 'warning', 'sort' => 2],
            ['name' => 'expired', 'color' => 'danger', 'sort' => 3],
            ['name' => 'suspended', 'color' => 'danger', 'sort' => 4],
        ];

        foreach ($statuses as $status) {
            PermitStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}

