<?php

namespace App\Filament\Resources\SponsorshipResource\Pages;

use App\Filament\Resources\SponsorshipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSponsorship extends CreateRecord
{
    protected static string $resource = SponsorshipResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
