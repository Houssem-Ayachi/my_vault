<?php

namespace App\Utils;

class Crypto {
    // On utilise AES-256 en mode GCM (Galois/Counter Mode).
    // C'est le standard actuel : il assure confidentialité ET intégrité.
    private const METHOD = 'aes-256-gcm';

    /**
     * Chiffre une donnée
     * Retourne une chaîne JSON contenant tout le nécessaire (Cipher + IV + Tag)
    */
    public static function encrypt(string $plaintext, string $key): string {
        // 1. Générer un IV (Vecteur d'Initialisation) aléatoire de 12 octets (standard GCM)
        // JAMAIS d'IV statique !
        $ivLen = openssl_cipher_iv_length(self::METHOD);

        $iv = random_bytes($ivLen);

        // 2. Variable pour récupérer le Tag d'authentification (spécifique GCM)
        $tag = "";

        // 3. Chiffrement
        // OPENSSL_RAW_DATA car on veut du binaire pur, on encodera nous-mêmes après
        $ciphertext = openssl_encrypt($plaintext, self::METHOD, $key, OPENSSL_RAW_DATA, $iv, $tag);

        // 4. On retourne un "package" complet encodé en Base64 pour être stocké en texte
        return json_encode([
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag) // Le tag prouve que le message n'a pas été altéré
        ]);
    }

    /**
    * Déchiffre une donnée
    * Attend le format JSON généré par encrypt()
    */
    public static function decrypt(string $jsonPayload, string $key): ?string
    {
        // 1. On décode le paquet
        $data = json_decode($jsonPayload, true);

        if (!isset($data['ciphertext'], $data['iv'], $data['tag'])) {
            return null; // Données corrompues
        }

        // 2. On repasse du Base64 au Binaire
        $ciphertext = base64_decode($data['ciphertext']);

        $iv = base64_decode($data['iv']);

        $tag = base64_decode($data['tag']);

        // 3. Déchiffrement
        $plaintext = openssl_decrypt($ciphertext, self::METHOD, $key, OPENSSL_RAW_DATA, $iv, $tag);

        // Si le tag est invalide (tentative de piratage), openssl retourne false
        return $plaintext !== false ? $plaintext : null;
    }
}