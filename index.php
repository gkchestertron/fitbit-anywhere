<?php 
require 'util.php';
$url = OAUTH_HREF . '?client_id=' . CLIENT_ID . '&response_type=' . RESPONSE_TYPE . '&scope=' . SCOPE . '&redirect_uri=' . REDIRECT_URI;
$code = $_GET['code'];
$fitbit = new FitBitConnection();
if (isset($code)) {
    $fitbit->get_oauth_tokens($code);
    $data = $fitbit->get_user_data('activities', '2015-10-27');
}
else if ($_SESSION['oauth']) {
    $data = $fitbit->get_user_data('activities', '2015-10-27');
}
?>
<!doctype html>
<html>
    <head></head>
    <body>
        <?php if (!isset($code)) { ?>
        <a href="<?php echo($url);?>">login with fitbit</a>
        <?php } ?>
        <?php if (isset($data)) { ?>
        <p>token request response: <?php echo($data); ?></p>
        <?php } ?>
<?php echo('session count: ' . $_SESSION['auth_header']); ?>
    </body>
</html>
