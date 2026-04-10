<?php

namespace Database\Seeders;

use App\Models\DemandeMotif;
use App\Models\DemandeReservation;
use Illuminate\Database\Seeder;

class DemandeMotifSeeder extends Seeder
{
    /** Sample image URLs (Unsplash) cycled as `file_path` for seeded motifs. */
    private const SAMPLE_IMAGE_URLS = [
        'https://images.unsplash.com/photo-1503023345310-bd7c1de61c7d',
        'https://images.unsplash.com/photo-1492724441997-5dc865305da7',
        'https://images.unsplash.com/photo-1519125323398-675f0ddb6308',
        'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee',
        'https://images.unsplash.com/photo-1521335629791-ce4aec67dd53',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
        'https://images.unsplash.com/photo-1495567720989-cebdbdd97913',
        'https://images.unsplash.com/photo-1472214103451-9374bd1c798e',
    ];

    public function run(): void
    {
        $demandes = DemandeReservation::query()->orderBy('id')->get();

        if ($demandes->isEmpty()) {
            return;
        }

        $samples = [
            ['motifs_description' => 'Justificatif budget'],
            ['motifs_description' => 'Bon de commande'],
            ['motifs_description' => 'Photo véhicule souhaité'],
        ];

        $urls = self::SAMPLE_IMAGE_URLS;

        foreach ($demandes as $i => $demande) {
            $meta = $samples[$i % count($samples)];
            $filePath = $urls[$i % count($urls)];

            DemandeMotif::query()->updateOrCreate(
                [
                    'demandes_reservation_id' => $demande->id,
                    'motifs_description' => $meta['motifs_description'],
                ],
                [
                    'file_path' => $filePath,
                    'file_type' => 'image/jpeg',
                ]
            );
        }
    }
}
