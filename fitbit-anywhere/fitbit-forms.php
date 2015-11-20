<?php
add_action( 'wp_enqueue_scripts', 'fitbit_enqueue' );

function fitbit_enqueue($hook) {
    wp_enqueue_script( 'ajax-script', plugins_url( '/fitbit-ajax.js', __FILE__ ), array('jquery') );

    // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
    wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}

add_action( 'wp_ajax_nopriv_fitbit_food_log_action', 'fitbit_food_log_callback' );
add_action( 'wp_ajax_fitbit_food_log_action', 'fitbit_food_log_callback' );

function fitbit_food_log_callback() {
    global $fitbit_connection;

    // create session if it doesn't exist 
    if (session_status() == PHP_SESSION_NONE) { 
            session_start();
    }

    $fitbit_connection->init();

    if (isset($fitbit_connection->oauth) && isset($fitbit_connection->oauth->access_token)) {
        $fitbit_connection->get_user_data('activities', '2015-11-16');
    }

    echo($fitbit_connection->{'activities'}['json']);

    wp_die(); // this is required to terminate immediately and return a proper response
}
