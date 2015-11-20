#fitbit-anywhere wordpress plugin
This is a wordpress plugin for the fitbit api. It contains a simple class for 
hooking up to the fitbit api from php. This uses php's built in curl utility for 
the requests. You just have to enter your own app id and secret from fitbit to get going. 
Don't forget to add the correct redirect urls to the config and your app settings with fitbit.
Also rename config\_sample.php to fitbit-config.php.

##To Use as a WordPress Plugin
To use as a plugin, just copy this folder into wp-content/plugins/ (unless you have moved that directory).
Make the appropriate changes to fitbit-config.php.
Then activate the plugin through the admin ui.
Included is a sample.txt file that shows how to use the shortcodes with the api.
