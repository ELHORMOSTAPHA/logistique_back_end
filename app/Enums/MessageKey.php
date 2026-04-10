<?php
// app/Enums/MessageKey.php
namespace App\Enums;

enum MessageKey: string
{
    // success
    case FETCHED = 'messages.success.fetched';
    case CREATED = 'messages.success.created';
    case UPDATED = 'messages.success.updated';
    case DELETED = 'messages.success.deleted';
    case USER_REGISTERED = 'messages.success.user_registered';
    case LOGIN_SUCCESS = 'messages.success.login';
    case TOKEN_REFRESHED = 'messages.success.token_refreshed';
    case LOGOUT_SUCCESS = 'messages.success.logout';

    // errors
    case NOT_FOUND  = 'messages.error.not_found';
    case SERVER     = 'messages.error.server';
    case FORBIDDEN  = 'messages.error.forbidden';
    case INVALID    = 'messages.error.invalid';
    case ROUTE      = 'messages.error.route';

    case AUTH_TOKEN_MISSING = 'messages.error.auth_token_missing';
    case AUTH_TOKEN_TYPE    = 'messages.error.auth_token_type';
    case AUTH_TOKEN_INVALID = 'messages.error.auth_token_invalid';
    case USER_NOT_FOUND     = 'messages.error.user_not_found';
    case AUTH_INVALID_CREDENTIALS = 'messages.error.auth_invalid_credentials';
    case ACCOUNT_INACTIVE   = 'messages.error.account_inactive';
    case AUTH_REFRESH_FAILED = 'messages.error.auth_refresh_failed';
    case AUTH_TOKEN_TYPE_MISMATCH = 'messages.error.auth_token_type_mismatch';

    public function translate(): string
    {
        $line = trans($this->value, [], app()->getLocale());

        if ($line !== $this->value) {
            return $line;
        }

        $fallbackLocale = config('app.fallback_locale', 'en');
        if (app()->getLocale() !== $fallbackLocale) {
            $line = trans($this->value, [], $fallbackLocale);
            if ($line !== $this->value) {
                return $line;
            }
        }

        return $this->defaultLabel();
    }

    /**
     * Human-readable fallback when lang files are missing or keys are wrong.
     */
    public function defaultLabel(): string
    {
        return match ($this) {
            self::FETCHED => 'Données récupérées avec succès',
            self::CREATED => 'Créé avec succès',
            self::UPDATED => 'Mis à jour avec succès',
            self::DELETED => 'Supprimé avec succès',
            self::USER_REGISTERED => 'Utilisateur enregistré avec succès',
            self::LOGIN_SUCCESS => 'Connexion réussie',
            self::TOKEN_REFRESHED => 'Jeton rafraîchi avec succès',
            self::LOGOUT_SUCCESS => 'Déconnexion réussie',
            self::NOT_FOUND => 'Ressource introuvable',
            self::SERVER => 'Une erreur est survenue',
            self::FORBIDDEN => 'Action non autorisée',
            self::INVALID => 'Données invalides',
            self::ROUTE => 'Route introuvable',
            self::AUTH_TOKEN_MISSING => 'Jeton non fourni',
            self::AUTH_TOKEN_TYPE => 'Type de jeton invalide. Utilisez le jeton d\'accès.',
            self::AUTH_TOKEN_INVALID => 'Jeton invalide ou expiré',
            self::USER_NOT_FOUND => 'Utilisateur non trouvé',
            self::AUTH_INVALID_CREDENTIALS => 'Identifiants invalides',
            self::ACCOUNT_INACTIVE => 'Votre compte n\'est pas actif. Veuillez contacter le support m-automotiv',
            self::AUTH_REFRESH_FAILED => 'Jeton de rafraîchissement invalide ou expiré',
            self::AUTH_TOKEN_TYPE_MISMATCH => 'Type de jeton invalide',
        };
    }
}