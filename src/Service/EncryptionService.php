<?php

namespace App\Service;

use Exception;

class EncryptionService
{
    private string $encryptionKey;
    private string $cipherMethod = 'AES-256-CBC';

    public function __construct(string $appSecret)
    {
        // Utiliser APP_SECRET comme base pour la clé de chiffrement
        // Créer une clé de 32 bytes (256 bits) pour AES-256
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    /**
     * Chiffre une donnée
     */
    public function encrypt(?string $data): ?string
    {
        if ($data === null || $data === '') {
            return null;
        }

        try {
            // Générer un vecteur d'initialisation aléatoire
            $ivLength = openssl_cipher_iv_length($this->cipherMethod);
            $iv = openssl_random_pseudo_bytes($ivLength);

            // Chiffrer les données
            $encrypted = openssl_encrypt(
                $data,
                $this->cipherMethod,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                throw new Exception('Échec du chiffrement');
            }

            // Combiner IV et données chiffrées, puis encoder en base64
            return base64_encode($iv . $encrypted);
        } catch (Exception $e) {
            // Log l'erreur (vous pouvez injecter un logger ici)
            throw new Exception('Erreur lors du chiffrement : ' . $e->getMessage());
        }
    }

    /**
     * Déchiffre une donnée
     */
    public function decrypt(?string $data): ?string
    {
        if ($data === null || $data === '') {
            return null;
        }

        try {
            // Décoder depuis base64
            $data = base64_decode($data, true);

            if ($data === false) {
                throw new Exception('Données invalides pour le déchiffrement');
            }

            // Extraire IV et données chiffrées
            $ivLength = openssl_cipher_iv_length($this->cipherMethod);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);

            // Déchiffrer
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->cipherMethod,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new Exception('Échec du déchiffrement');
            }

            return $decrypted;
        } catch (Exception $e) {
            // Log l'erreur
            throw new Exception('Erreur lors du déchiffrement : ' . $e->getMessage());
        }
    }

    /**
     * Vérifie si une chaîne est chiffrée (base64 valide)
     */
    public function isEncrypted(?string $data): bool
    {
        if ($data === null || $data === '') {
            return false;
        }

        // Vérifier si c'est du base64 valide
        $decoded = base64_decode($data, true);
        return $decoded !== false && base64_encode($decoded) === $data;
    }
}
