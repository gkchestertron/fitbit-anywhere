<?php
require 'fitbit-config.php';

class FitbitConnection {
    function init() {
        if (isset($_SESSION['oauth_json']) && isset($_SESSION['auth_header'])) {
            $this->oauth = json_decode($_SESSION['oauth_json']);
            $this->auth_header = $_SESSION['auth_header'];
        }
    }

    function do_get_request($url, $optional_headers = null) {
        $headers = array("Content-Type: application/x-www-form-urlencoded", "Accept-Language: en_US");
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL,$url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        if (isset($optional_headers)) {
            $headers = array_merge($headers, $optional_headers);
        }

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);

        curl_close($request);

        return array($server_output, $status);
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
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);

        curl_close($request);

        return array($server_output, $status);
    }

    function get_oauth_tokens($code) {
        $auth_header   = array("Authorization: Basic " . base64_encode(FITBIT_CLIENT_ID . ":" . FITBIT_CLIENT_SECRET));

        $token_request_href = FITBIT_OAUTH_TOKEN_HREF . 
            '?code=' . $code . 
            '&grant_type='   . FITBIT_GRANT_TYPE . 
            '&client_id='    . FITBIT_CLIENT_ID . 
            '&redirect_uri=' . FITBIT_REDIRECT_URI;

        list($json, $status) = $this->do_post_request($token_request_href, null, $auth_header);

        $this->oauth = json_decode($json);
        $this->auth_header = 'Authorization: Bearer ' . $this->oauth->access_token;

        $_SESSION['auth_header'] = $this->auth_header;
        $_SESSION['oauth_json'] = $json;
    }

    function get_user_data($data_path, $date = null, $period = null) {
        if (!isset($date)) {
            $url = FITBIT_API_URL . $this->oauth->user_id . '/' . $data_path . '.json';
        }
        else if (isset($date) && isset($period)) {
            $url = FITBIT_API_URL . $this->oauth->user_id . '/' . $data_path . '/date/' . $date . '/' . $period .'.json';
        }
        else {
            $url = FITBIT_API_URL . $this->oauth->user_id . '/' . $data_path . '/date/' . $date . '.json';
        }

        list($json, $status) = $this->do_get_request($url, array($this->auth_header));
        if ($status == 401 && isset($this->oauth->refresh_token)) {
            $this->refresh_access_token();
            list($json, $status) = $this->do_get_request($url, array($this->auth_header));
        }

        $this->{$data_path} = array("json" => $json, "status" => $status, "data" => json_decode($json));
    }

    function get_top_level_data($data_path, $query = null) {
        $url = FITBIT_TOP_LEVEL_URL . $data_path . '.json';
            
        if (isset($query)) {
            $url .= '?query=' . $query;
        }

        list($json, $status) = $this->do_get_request($url, array($this->auth_header));

        if ($status == 401 && isset($this->oauth->refresh_token)) {
            $this->refresh_access_token();
            list($json, $status) = $this->do_get_request($url, array($this->auth_header));
        }

        if (!isset($this->top_level)) {
            $this->top_level = new stdClass();
        }

        $this->top_level->{$data_path} = array("json" => $json, "status" => $status, "data" => json_decode($json));
    }

    function refresh_access_token() {
        $auth_header   = array("Authorization: Basic " . base64_encode(FITBIT_CLIENT_ID . ":" . FITBIT_CLIENT_SECRET));
        $refresh_token_request_href = FITBIT_OAUTH_TOKEN_HREF . 
            '?grant_type='   . FITBIT_REFRESH_GRANT_TYPE .
            '&refresh_token=' . $this->oauth->refresh_token;

        list($json, $status) = $this->do_post_request($refresh_token_request_href, null, $auth_header);

        $this->oauth = json_decode($json);
        $this->auth_header = 'Authorization: Bearer ' . $this->oauth->access_token;

        $_SESSION['auth_header'] = $this->auth_header;
        $_SESSION['oauth_json'] = $json;
    }
}
