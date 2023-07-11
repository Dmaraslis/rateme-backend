<?php
final class Encryption {
    private $key;

    public function __construct($key = 'INSTASWAPFUCKINGOWNERS') {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt($message)
    {
        $nonce = random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );

        $cipher = base64_encode(
            $nonce.
            sodium_crypto_secretbox(
                $message,
                $nonce,
                $this->key
            )
        );
        sodium_memzero($message);
        sodium_memzero($this->key);
        return $cipher;
    }

    public function decrypt($encrypted)
    {
        $decoded = base64_decode($encrypted);
        if ($decoded === false) {
            throw new Exception('Scream bloody murder, the encoding failed');
        }
        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new Exception('Scream bloody murder, the message was truncated');
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $this->key
        );
        if ($plain === false) {
            throw new Exception('the message was tampered with in transit');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($this->key);
        return $plain;
    }



    public function safeEncrypt($message, $key)
    {
        $message = str_replace(' ','-',$message);
        $key = hash('sha256', $key, true);
        $nonce = random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );

        $cipher = base64_encode(
            $nonce.
            sodium_crypto_secretbox(
                $message,
                $nonce,
                $key
            )
        );
        sodium_memzero($message);
        sodium_memzero($key);
        $cipher = str_replace('+','_',$cipher);
        return $cipher;
    }

    public function safeDecrypt($encrypted, $key)
    {
        $encrypted = str_replace('_','+',$encrypted);
        $key = hash('sha256', $key, true);
        $decoded = base64_decode($encrypted);
        if ($decoded === false) {
            throw new Exception('Scream bloody murder, the encoding failed');
        }
        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new Exception('Scream bloody murder, the message was truncated');
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );
        if ($plain === false) {
            throw new Exception('the message was tampered with in transit');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);
        $plain = str_replace('-',' ',$plain);
        return $plain;
    }
}