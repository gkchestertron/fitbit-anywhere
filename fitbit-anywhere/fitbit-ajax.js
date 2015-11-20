jQuery(document).ready(function($) {
    var data = {
        'action': 'fitbit_food_log_action',
        'whatever': ajax_object.we_value      // We pass php values differently!
    };
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post(ajax_object.ajax_url, data, function(response) {
        window.fitbit_activities = JSON.parse(response);
    });
});
