<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\IntegrationClient;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('integration:client:create {name}', function () {
    $name = (string) $this->argument('name');

    $clientId = 'icl_' . Str::lower(Str::random(20));
    $clientSecretPlain = 'ics_' . Str::random(48);

    $client = IntegrationClient::create([
        'name' => $name,
        'client_id' => $clientId,
        'client_secret' => Hash::make($clientSecretPlain),
        'is_active' => true,
    ]);

    $this->info('Integration client created successfully.');
    $this->line('ID: ' . $client->id);
    $this->line('client_id: ' . $client->client_id);
    $this->line('client_secret: ' . $clientSecretPlain);
    $this->warn('Save the client_secret now. It will not be shown again.');
})->purpose('Create an integration API client');
