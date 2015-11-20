<?php
/*
* Plugin Name: Fitbit Anywhere
* Description: Fitbit API Integration
* Version: 1.0
* Author: gkchestertron
* Author URI: https://github.com/gkchestertron
*/

require "fitbit-connection.php";
require "fitbit-forms.php";

$fitbit_connection = new FitbitConnection();

// make shortcode for accessing feature data
function fitbit_anywhere_shortcode($atts, $content = null) {
    global $fitbit_connection;

    $feature = $fitbit_connection->{$atts['feature']};

    if (!isset($feature)) {
        return '';
    }

    if ($atts['key'] == 'json') {
        return $feature['json'];
    }


    if ($content == null) {
        $data = get_data_from_fitbit_shortcode($feature['data'], $atts['key']);
        return $data;
    }

    $data = get_data_from_fitbit_shortcode($feature['data'], $atts['key'], false);
    return replace_nested_fitbit_shortcodes($data, html_entity_decode($content), true);
}

function fitbit_anywhere_form_shortcode($atts) {
    global $fitbit_connection;

    $fitbit_connection->get_top_level_data('foods/search', 'apple');
    return $fitbit_connection->top_level->{'foods/search'}['json'];
}

function replace_nested_fitbit_shortcodes($data, $content, $iterate = true) {
    // get tag sets that are further nested
    $nested_re = '/\[fitbit_data key=.(.*?).](.*?)\[\/fitbit_data]/msu';
    $final_re  = '/\[fitbit_data key=.(.*?).]/msu';
    $meta_result = '';

    if (!$iterate) {
        $data = [$data];
    }

    foreach ($data as $data_el) {
        preg_match_all($nested_re, $content, $nested_matches);
        $nested_split = preg_split($nested_re, $content);
        $result = $nested_split[0];
        for ($i = 0; $i < count($nested_matches[0]); $i++) {
            $match_key     = $nested_matches[1][$i];
            $match_content = $nested_matches[2][$i];
            $next_split = $nested_split[$i + 1];

            if (strlen($match_content)) {
                $match_data = get_data_from_fitbit_shortcode($data_el, $match_key, false);
                $result .= (replace_nested_fitbit_shortcodes($match_data, $match_content) . $next_split);
            }
            else {
                $match_data = get_data_from_fitbit_shortcode($data_el, $match_key);
                $result .= ($match_data . $next_split);
            }
        }

        preg_match_all($final_re, $result, $final_matches);
        $final_split = preg_split($final_re, $result);
        $result      = $final_split[0];

        for ($i = 0; $i < count($final_matches[0]); $i++) {
            $match_key  = $final_matches[1][$i];
            $next_split = $final_split[$i + 1];
            $match_data = get_data_from_fitbit_shortcode($data_el, $match_key);
            $result .= ($match_data . $next_split);
        }

        $meta_result .= $result;
    }

    return $meta_result;
}

function get_data_from_fitbit_shortcode($value, $key_string, $stringify = true) {
    if (isset($key_string)) {
        $keys = explode(' ', $key_string);
        foreach ($keys as $key) {
            try {
                // if we want an image tag returned
                if ($key == 'as_image' && gettype($value) == 'string') {
                    return '<img src="' . $value . '">';
                }
                // check for thing=other_thing syntax for finding obj in array
                if (strpos($key, '=') !== false) {
                    list($innerKey, $innerVal) = explode('=', $key);
                    foreach ($value as $obj) {
                        if ($obj->{$innerKey} == $innerVal) {
                            $value = $obj;
                            break;
                        }
                    }
                }
                else {
                    $value = $value->{$key};
                }
            } catch (Exception $e) {
                return '';
            }
        }
    }

    if (!isset($value)) {
        $value = '';
    }

    if (gettype($value) == 'string' || !$stringify) {
        return $value;
    }
    else {
        return json_encode($value);
    }
}

// simple code for getting login with fitbit link
function fitbit_auth_link_shortcode($atts) {
    global $fitbit_connection;

    $url = FITBIT_OAUTH_HREF . '?client_id=' . FITBIT_CLIENT_ID . 
        '&response_type=' . FITBIT_RESPONSE_TYPE . 
        '&scope=' . FITBIT_SCOPE . 
        '&redirect_uri=' . FITBIT_REDIRECT_URI;

    if (!isset($fitbit_connection->oauth) || !isset($fitbit_connection->oauth->access_token)) {
        return '<a href="' . $url . '">login to fitbit</a>';
    }
    else {
        return 'you are logged in with fitbit';
    }
}

function load_fitbit_anywhere($atts) {
    global $fitbit_connection;

    // create session if it doesn't exist 
    if (session_status() == PHP_SESSION_NONE) { 
            session_start();
    }

    $fitbit_connection->init();

    $code   = $_GET['code'];
    $switch = $_GET['switch_fitbit_user'];

    if (isset($code)) {
        $fitbit_connection->get_oauth_tokens($code);            
        header("Location: " . FITBIT_REDIRECT_URI);
        exit();
    }
    else if (isset($switch)) {
        unset($_SESSION['auth_header']);
        unset($_SESSION['oauth_json']);
        header("Location: " . FITBIT_REDIRECT_URI);
        exit();
    }
    else if (isset($fitbit_connection->oauth) && isset($fitbit_connection->oauth->access_token)) {
        $date = $atts['date'];
        if ($date == 'today') {
            $date = date('Y-m-d');
        }
        $period = $atts['period'];
        $fitbit_connection->get_user_data($atts['feature'], $date, $period);
    }

}

// load on the "wp" hook
add_shortcode('fitbit_load', 'load_fitbit_anywhere');
add_shortcode('fitbit_api', 'fitbit_anywhere_shortcode');
add_shortcode('fitbit_auth_link', 'fitbit_auth_link_shortcode');
add_shortcode('fitbit_form', 'fitbit_anywhere_form_shortcode');
