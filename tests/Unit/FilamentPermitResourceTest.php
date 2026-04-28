<?php

namespace Tests\Unit;

use App\Filament\Resources\Permits\PermitResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Components\Component;
use Livewire\Component as LivewireComponent;
use Tests\TestCase;

class FilamentPermitResourceTest extends TestCase
{
    public function test_permit_resource_form_uses_permit_holder_and_vehicle_relationships(): void
    {
        $livewire = new class extends LivewireComponent implements HasSchemas {
            public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
            {
                return null;
            }

            public function getOldSchemaState(string $statePath): mixed
            {
                return null;
            }

            public function getSchemaComponent(string $key, bool $withHidden = false, array $skipComponentsChildContainersWhileSearching = []): Component | null
            {
                return null;
            }

            public function getSchema(string $name): ?Schema
            {
                return null;
            }

            public function currentlyValidatingSchema(?Schema $schema): void
            {
            }

            public function getDefaultTestingSchemaName(): ?string
            {
                return null;
            }
        };

        $schema = Schema::make()->livewire($livewire);
        $fields = PermitResource::form($schema)->getFlatFields();

        $holderField = array_values(array_filter($fields, fn ($field) => $field->getStatePath() === 'permit_holder_id'))[0] ?? null;
        $vehicleField = array_values(array_filter($fields, fn ($field) => $field->getStatePath() === 'vehicle_id'))[0] ?? null;

        $this->assertNotNull($holderField, 'Expected permit_holder_id field in PermitResource form.');
        $this->assertNotNull($vehicleField, 'Expected vehicle_id field in PermitResource form.');

        $this->assertSame('permitHolder', $holderField->getRelationshipName());
        $this->assertSame('vehicle', $vehicleField->getRelationshipName());
    }
}
