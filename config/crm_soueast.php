<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM SOUEAST — Référentiel véhicule
    |--------------------------------------------------------------------------
    |
    | Configuration pour la synchronisation des tables référentiel véhicule
    | (car_marques, car_modeles, car_finitions, crm_vehicules_colors) depuis
    | l'API CRM SOUEAST. Les valeurs sont surchargeables via le fichier .env.
    |
    */

    'base_url' => env('CRM_SOUEAST_BASE_URL', 'https://soueast-api.onetechapp.ma'),

    'api_prefix' => env('CRM_SOUEAST_API_PREFIX', '/api/v1'),

    'username' => env('CRM_SOUEAST_USERNAME', 'admin'),

    'password' => env('CRM_SOUEAST_PASSWORD'),

    'timeout' => (int) env('CRM_SOUEAST_TIMEOUT', 30),

    // Durée de mise en cache du bearer (en minutes). Doit rester < SANCTUM_TOKEN_EXPIRATION côté CRM (1440 = 24h).
    'token_ttl_minutes' => (int) env('CRM_SOUEAST_TOKEN_TTL_MINUTES', 23 * 60),

    // Secret partagé attendu par le middleware `cron.secret` pour les déclenchements depuis un cron serveur.
    // À envoyer dans l'en-tête `X-Cron-Secret: <secret>` (ou `Authorization: Bearer <secret>`).
    'cron_secret' => env('CRON_SECRET', ''),
];
