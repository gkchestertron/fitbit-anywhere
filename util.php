<?php
require('config.php');
session_start();
if (isset($_SESSION['count'])) {
    $_SESSION['count']++;
}
else {
    $_SESSION['count'] = 0;
}

class FitBitConnection {
    function __construct() {
        if (isset($_SESSION['oauth_json']) && isset($_SESSION['auth_header'])) {
            $this->oauth = json_decode($_SESSION['oauth_json']);
            $this->auth_header = $_SESSION['auth_header'];
        }
    }

    function do_get_request($url, $optional_headers = null) {
        $headers = array("Content-Type: application/x-www-form-urlencoded");
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL,$url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        if (isset($optional_headers)) {
            $headers = array_merge($headers, $optional_headers);
        }

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($request);

        curl_close($request);

        return $server_output;
    }

    function do_post_request($url, $data, $optional_headers = null) {
        $headers = array("Content-Type: application/x-www-form-urlencoded");
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL,$url);
        curl_setopt($request, CURLOPT_POST, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS,$data);  //Post Fields
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        if (isset($optional_headers)) {
            $headers = array_merge($headers, $optional_headers);
        }

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($request);

        curl_close($request);

        return $server_output;
    }

    function get_oauth_tokens($code) {
        $auth_header   = array("Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET));

        $token_request_href = OAUTH_TOKEN_HREF . 
            '?code=' . $code . 
            '&grant_type='   . GRANT_TYPE . 
            '&client_id='    . CLIENT_ID . 
            '&redirect_uri=' . REDIRECT_URI;

        $json = $this->do_post_request($token_request_href, null, $auth_header);

        $this->oauth = json_decode($json);
        $this->auth_header = 'Authorization: Bearer ' . $this->oauth->access_token;

        $_SESSION['auth_header'] = $this->auth_header;
        $_SESSION['oauth_json'] = $json;
    }

    function get_user_data($data_path, $date) {
        $url = API_URL . $this->oauth->user_id . '/' . $data_path . '/date/' . $date . '.json';
        return $this->do_get_request($url, array($_SESSION['auth_header']));
    }
}
