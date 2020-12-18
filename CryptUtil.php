<?php
namespace Sentia\Utils;

use Exception;

class CryptUtil {
    /**
     * crypt message (using sodium)
     */
    public function crypt(string $message, string $key):?string{
        try{
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipher = base64_encode($nonce . sodium_crypto_secretbox($message, $nonce, $key));
            sodium_memzero($message);
            sodium_memzero($key);
            return $cipher;
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * Decrypt a message (using sodium)
     */
    public function decrypt(string $encrypted, string $key):?string{
        $decoded = base64_decode($encrypted);
        if ($decoded === false) {
            return null;
        }
        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            return null;
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        try{
            $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
            if($plain === false){
                return null;
            }
            sodium_memzero($ciphertext);
            sodium_memzero($key);
            return $plain;
        }catch(Exception $e){
            return null;
        }
    }

}
