<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    //generate jwtoken

    /**
     * Generation of JWT
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        //encoding in base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        //cleaning encoded values (from +, /, =) 
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        //gererating signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        //creating token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;



        return $jwt;
    }
    //additional functions to check validity and format of token////
    public function getPayload(string $token): array
    {
        //desassembling token
        $array = explode('.', $token);
        //decoding payload (second element in array)
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }
    public function getHeader(string $token): array
    {
        //desassembling token
        $array = explode('.', $token);
        //decoding payload (second element in array)
        $payload = json_decode(base64_decode($array[0]), true);

        return $payload;
    }
    ////////////////////////////////////////////////////////////////

    //verification of validity of token (fits proper format)
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_]+\.[a-zA-Z0-9\-\_]+\.[a-zA-Z0-9\-\_]+$/',
            $token
        ) === 1;
    }

    //verification of validity of token (not expired, not changed)
    public function isExpired($token): bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //verifying signature
    public function checkSignature(string $token, string $secret)
    {
        //recovering header and payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //regenerate token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }
}
