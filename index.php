<?php 
require 'fitbit.php';
$url = FITBIT_OAUTH_HREF . '?client_id=' . FITBIT_CLIENT_ID . '&response_type=' . FITBIT_RESPONSE_TYPE . '&scope=' . FITBIT_SCOPE . '&redirect_uri=' . FITBIT_REDIRECT_URI;
$code = $_GET['code'];
$fitbit = new FitBitConnection();
if (isset($code)) {
    $fitbit->get_oauth_tokens($code);
    header("Location: " . FITBIT_REDIRECT_URI);
    exit();
}
else {
    list($data, $status) = $fitbit->get_user_data('activities', date('Y-m-d'));
}
?>
<!doctype html>
<html>
    <head></head>
    <body>
        <?php if (!isset($fitbit->oauth) || !isset($fitbit->oauth->access_token)) { ?>
        <a href="<?php echo($url);?>">login with fitbit</a>
        <?php } ?>
        <?php 
            if (isset($status) && $status < 400) {
                echo($fitbit->buildHtml(json_decode($data)));
            }
            else {
                echo($status);
            }
        ?>
    </body>
</html>
