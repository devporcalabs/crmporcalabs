<?php

namespace App\Filament\Resources\FreelancerResource\Pages;

use App\Filament\Resources\FreelancerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFreelancers extends ListRecords
{
    protected static string $resource = FreelancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
