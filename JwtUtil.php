<?php
namespace Sentia\Utils;
/**
 * JSON Web Token implementation, based on this spec:
 * http://tools.ietf.org/html/draft-ietf-oauth-json-web-token-06
 */
class JwtUtil {

    const HS256 = 'sha256';
    const HS384 = 'sha384';
    const HS512 = 'sha512';

    public function __construct(){
    }

    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param string      $jwt    The JWT
     * @param string|null $key    The secret key
     * @param bool        $verify Don't skip verification process
     *
     * @return object      The JWT's payload as a PHP object
     * @throws \UnexpectedValueException Provided JWT was invalid
     * @throws \DomainException          Algorithm was not provided
     *
     * @uses jsonDecode
     * @uses urlsafeB64Decode
     */
    public function decode(string $jwt, ?string $key = null, bool $verify = true){
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = $this->jsonDecode($this->urlsafeB64Decode($headb64)))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }
        if (null === $payload = $this->jsonDecode($this->urlsafeB64Decode($bodyb64))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }
        $sig = $this->urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new \DomainException('Empty algorithm');
            }
            if ($sig != $this->sign("$headb64.$bodyb64", $key, $header->alg)) {
                throw new \UnexpectedValueException('Signature verification failed');
            }
        }
        return $payload;
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm. Supported algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string      A signed JWT
     * @uses jsonEncode
     * @uses urlsafeB64Encode
     */
    /*
payload
    'iss' - sentia
    'exp' - [now+15 minutes] current dateTime MUST be before this value (timestamp)
    'iat' - the time at which the JWT was issued (timestamp)
    'idUser'
    'userName'
    */
    public function encode(array $payload, string $key, string $algo = self::HS256){
        $header = ['typ' => 'JWT', 'alg' => $algo];

        $segments = [];
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($payload));

        $signing_input = implode('.', $segments);
        $signature = $this->sign($signing_input, $key, $algo);
        $segments[] = $this->urlsafeB64Encode($signature);
        return implode('.', $segments);
    }

    /**
     * Sign a string with a given key and algorithm.
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $algo The signing algorithm. Supported
     *                       algorithms are 'HS256', 'HS384' and 'HS512'
     * @return string          An encrypted message
     * @throws \DomainException Unsupported algorithm was specified
     */
    public function sign(string $msg, string $key, string $algo = self::HS256):string{
        return hash_hmac($algo, $msg, $key, true);
    }

    /**
     * Decode a JSON string into a PHP object.
     * @param string $input JSON string
     * @return object          Object representation of JSON string
     * @throws \DomainException Provided string was invalid JSON
     */
    public function jsonDecode($input){
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            $this->_handleJsonError($errno);
        } else if ($obj === null && $input !== 'null') {
            throw new \DomainException('Null result with non-null input');
        }
        return $obj;
    }

    /**
     * Encode a PHP object into a JSON string.
     * @param object|array $input A PHP object or array
     * @return string          JSON representation of the PHP object or array
     * @throws \DomainException Provided object could not be encoded to valid JSON
     */
    public function jsonEncode($input):string{
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            $this->_handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new \DomainException('Null result with non-null input');
        }
        return $json;
    }

    /**
     * Decode a string with URL-safe Base64.
     * @param string $input A Base64 encoded string
     * @return string A decoded string
     */
    public function urlsafeB64Decode(string $input):string{
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL-safe Base64.
     * @param string $input The string you want encoded
     * @return string The base64 encode of what you passed in
     */
    public function urlsafeB64Encode(string $input):string{
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * Helper method to create a JSON error.
     * @param int $errno An error number from json_last_error()
     */
    private function _handleJsonError(int $errno):void{
        $messages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        ];
        throw new \DomainException(isset($messages[$errno]) ? $messages[$errno] : 'Unknown JSON error: ' . $errno);
    }

}
