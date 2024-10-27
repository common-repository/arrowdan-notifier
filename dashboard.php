<?php
if (!defined('ABSPATH')) {exit;}
?>
<h1><?php echo __(AFCMPN_PLUGIN_NM, AFCMPN_T);?></h1>
<p>Note: By default it uses topic 'post'</p>
<form action="options.php" method="post">
<?php settings_fields( 'afcmpn_group'); ?>
<?php do_settings_sections( 'afcmpn_group' ); ?>
<table style="width: 100%;">
    <tbody>
        <tr height="50">
            <td style="width: 30%;"><b><label for="afcmpn_api"><?php echo __("Server API Key", AFCMPN_T);?></label></b></td>
            <td style="width: 70%;"><input style="width: 50%;" id="afcmpn_api" name="afcmpn_api" type="text" value="<?php echo get_option( 'afcmpn_api' ); ?>" required="required" placeholder="Enter Valid FCM Server API Key"></td>
        </tr>
        <tr height="50">
            <td style="width: 30%;"><b><label for="afcmpn_disable_image"><?php echo __("Disable Image Notification", AFCMPN_T);?></label></b></td>
            <td><input id="afcmpn_disable_image" name="afcmpn_disable_image" type="checkbox" value="1" <?php checked( '1', get_option( 'afcmpn_disable_image' ) ); ?>></td>
        </tr>
        <tr height="50">
            <td style="width: 30%;"><b><label for="afcmpn_disable"><?php echo __("Disable Push Notification", AFCMPN_T);?></label></b></td>
            <td><input id="afcmpn_disable" name="afcmpn_disable" type="checkbox" value="1" <?php checked( '1', get_option( 'afcmpn_disable' ) ); ?>></td>
        </tr>
        <tr>
            <td colspan="2"><?php submit_button(); ?></td>
        </tr>
    </tbody>
</table>
<?php if(get_option('afcmpn_api')){ ?>
<div>
    <h3>Test Notification</h3>
    <p>Notification will sent to all device subscribed on topic 'post'</p>
    <a href="<?php echo admin_url('admin.php'); ?>?page=arrowdan-test-notification">Test Notification</a>
</div>
<?php } ?>