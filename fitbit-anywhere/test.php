<?php
function replace_nested_fitbit_shortcodes($data, $content) {
    // get tag sets that are further nested
    $nested_re = '/\[fitbit_data key="(.*?)"](.*?)\[\/fitbit_data]/';
    $final_re  = '/\[fitbit_data key="(.*?)"]/';

    preg_match_all($nested_re, $content, $nested_matches);
    $nested_split = preg_split($nested_re, $content);
    $result = $nested_split[0];

    for ($i = 0; $i < count($nested_matches[0]); $i++) {
        $match_key     = $nested_matches[1][$i];
        $match_content = $nested_matches[2][$i];
        $next_split = $nested_split[$i + 1];
        $match_data = get_data_from_fitbit_shortcode($data, $match_key, false);
        $result .= (replace_nested_fitbit_shortcodes($match_data, $match_content) . $next_split);
    }

    preg_match_all($final_re, $result, $final_matches);
    $final_split = preg_split($final_re, $result);
    $result      = $final_split[0];

    for ($i = 0; $i < count($final_matches[0]); $i++) {
        $match_key  = $final_matches[1][$i];
        $next_split = $final_split[$i + 1];
        $match_data = get_data_from_fitbit_shortcode($data, $match_key);
        $result .= ($match_data . $next_split);
    }

    return $result;
}

function get_data_from_fitbit_shortcode($value, $key_string, $stringify = true) {
    $keys = explode(' ', $key_string);
    foreach ($keys as $key) {
        try {
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
            print('threw an error');
            return '';
        }
    }

    if (gettype($value) == 'string' || !$stringify) {
        return $value;
    }
    else {
        return json_encode($value);
    }
}

$d = json_decode('{"shit":{"fuck":"up"}}');

print replace_nested_fitbit_shortcodes($d, '[fitbit_data key="shit"] fuck goes here: [fitbit_data key="fuck"] - [/fitbit_data]');
