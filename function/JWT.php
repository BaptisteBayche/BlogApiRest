<?php

function generate_jwt($headers, $payload, $secret = 'secret') {
    $headers_encoded = base64_encode(json_encode($headers));
    $payload_encoded = base64_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$headers_encoded . $payload_encoded" , $secret, true);
    $signature_encoded = base64url_encode($signature);

    $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
    
    return $jwt;
}
    function is_jwt_valid($jwt, $secret = 'secret'){

        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        $expiration = json_decode($payload)->exp;
        $is_token_expired = ($expiration - time()) < 0;

        $base64UrlHeader = base64_encode($header);
        $base64UrlPayload = base64_encode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader.".".$base64UrlPayload, $secret, true);
        $base64UrlSignature = base64_encode($signature);

        $is_signature_valid = ($base64UrlSignature === $signature_provided);

        if ($is_token_expired || !$is_signature_valid) {
            return false;
        }else{
            return true;
        }

    }

    function get_bearer_token(){
        $headers = get_authorization_header();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
    }

?>