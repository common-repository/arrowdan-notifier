<?php
/**
Plugin Name: ARROWDAN Notifier
Description: Notify users using Firebase Cloud Messaging (FCM) when post is published. 
Version: 1.0.1
Author: ArBn
Author URI: https://www.ar-bn.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {exit;}
if (!defined("AFCMPN_VERSION_CURRENT")) define("AFCMPN_VERSION_CURRENT", '1.0.1');
if (!defined("AFCMPN_URL")) define("AFCMPN_URL", plugin_dir_url( __FILE__ ) );
if (!defined("AFCMPN_PLUGIN_DIR")) define("AFCMPN_PLUGIN_DIR", plugin_dir_path(__FILE__));
if (!defined("AFCMPN_PLUGIN_NM")) define("AFCMPN_PLUGIN_NM", 'ARROWDAN Notifier');
if (!defined("AFCMPN_t")) define("AFCMPN_T", 'AFCMPN_t');
class ARROWDAN_FCM_PN
{
    public function __construct()
    {
        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array($this, 'afcmpn_activate'));
        register_deactivation_hook(__FILE__, array($this, 'afcmpn_deactivate'));
        add_action('admin_menu', array($this, 'afcmpn_admin_menu'));
        add_action('admin_init', array($this, 'afcmpn_settings'));
        add_action( 'save_post', array($this, 'afcmpn_meta_save'), 1);
        add_action( 'add_meta_boxes', array($this, 'afcmpn_featured_meta'), 1);
        add_filter( 'plugin_action_links_arrowdan-notifier/arrowdan-notifier.php', array($this, 'afcmpn_settings_link'));
    }

    function afcmpn_featured_meta() {       
        $args  = array(
            'public' => true,
        );        
        $post_types = get_post_types( $args, 'objects' );        
        if ( $post_types ) { // If there are any custom public post types.
            
            foreach ( $post_types  as $post_type ) {
                if ($post_type->name != 'attachment'){
                    if ($this->get_options_posttype($post_type->name) && get_option('afcmpn_disable') != 1) {
                        add_meta_box( 'afcmpn_ckmeta_send_notification', esc_attr(__( 'ARROWDAN Notifier', AFCMPN_T )), array($this, 'afcmpn_meta_callback'), $post_type->name, 'side', 'high', null );
                    }
                }
            }

        }
    }

    function get_options_posttype($post_type) {
        if ($post_type == "post") {
            return true;
        }
    }

    function afcmpn_admin_menu()
    {
        add_submenu_page('options-general.php', __('ARROWDAN Notifier', AFCMPN_T), AFCMPN_PLUGIN_NM, 'manage_options', 'arrowdan-notifier-settings', array($this, 'arrowdan_form'));
        add_submenu_page(null, __('ARROWDAN Test Notification', AFCMPN_T), 'Test Notification', 'administrator', 'arrowdan-test-notification', array($this, 'afcmpn_send_test_notification'));
    }

    function afcmpn_settings_link( $links )
    {
        static $this_plugin;

        if (!$this_plugin) {
            $this_plugin = plugin_basename(__FILE__);
        }
        // Build and escape the URL.
        $url = esc_url( add_query_arg(
            'page',
            'arrowdan-notifier-settings',
            get_admin_url() . 'admin.php'
        ) );
        // Create the link.
        $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }

    function afcmpn_settings()
    {
        register_setting('afcmpn_group', 'afcmpn_api');
        register_setting('afcmpn_group', 'afcmpn_disable_image');
        register_setting('afcmpn_group', 'afcmpn_disable');
    }

    public function arrowdan_form()
    {
        include(plugin_dir_path(__FILE__) . 'dashboard.php');
    }

    function afcmpn_send_test_notification()
    {
        $test = new ARROWDANTestNotifier;
        $test->ID = 0;
        $test->post_title = "Test Notification";
        $test->post_content = "Hi I am ArBn. You are using ARROWDAN Notifier.";
        $test->post_excerpt = $test->post_content;
        $result = $this->arrowdan_notifier($test);
        echo '<h1>'.__(AFCMPN_PLUGIN_NM, AFCMPN_T).'</h1>';
        echo '<div class="row">';
        echo '<div><h3>API Information</h3>';
        echo '<pre>';
        printf($result);
        echo '</pre>';
        echo '<p><a href="'.admin_url('admin.php').'?page=arrowdan-test-notification">Re-Send</a></p>';
        echo '<p><a href="'.admin_url('admin.php').'?page=arrowdan-notifier-settings">Setting</a></p>';
        echo '</div>';
    }

    function arrowdan_notifier($post)
    {
        $apiKey = get_option('afcmpn_api');
        $url = 'https://fcm.googleapis.com/fcm/send';

        $post_title = wp_strip_all_tags($post->post_title);        
        $post_content = _mb_strlen($post->post_excerpt) == 0 ? _mb_substr(wp_strip_all_tags($post->post_content), 0, 55) : wp_strip_all_tags($post->post_excerpt);        
        $post_id = esc_attr($post->ID);
        $thumb_id = get_post_thumbnail_id( $post_id );
        $thumb_url = wp_get_attachment_image_src( $thumb_id, 'full' );
        $image = $thumb_url ? esc_url($thumb_url[0]) : '';
        if (_mb_strlen($image) == 0 || get_option('afcmpn_disable_image') == 1) {
            $image = null;
        }
        $notification = array(
            'title'                 => $post_title,
            'body'                  => $post_content,
            'sound'                 => 'default',
            'click_action'          => 'FLUTTER_NOTIFICATION_CLICK',
            'image'                 => $image,   
        );
        $data = array(
            'message'               => $post_content,
            'title'                 => $post_title,
            'image'                 => $image,
            'id'                    => $post_id,
        );   
        $post = array(
            'condition'         => "'post' in topics",
            'notification'      => $notification,
            'content_available' => true,
            'priority'          => 'high',
            'data'              => $data
        );
        $response = array(
            'timeout'           => 45,
            'redirection'       => 5,
            'httpversion'       => '1.0',
            'method'            => 'POST',
            'body'              => json_encode($post),
            'sslverify'         => false,
            'cookies'           => array(),
            'headers'           => array(
                'Content-Type'      => 'application/json',
                'Authorization'     => 'key=' . $apiKey,
            ),
        );
        $result = wp_remote_post($url, $response);
        return json_encode($result);
    }

    function afcmpn_meta_save( $post_id ) {
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ 'afcmpn_nonce' ] ) && wp_verify_nonce( $_POST[ 'afcmpn_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }
        remove_action('wp_insert_post', array($this, 'afcmpn_post_save'),10);
    
        // Checks for input and saves - save checked as yes and unchecked at no
        if( isset( $_POST[ 'send-fcm-checkbox' ] ) ) {
            update_post_meta( $post_id, 'send-fcm-checkbox', '1' );
        } else {
            update_post_meta( $post_id, 'send-fcm-checkbox', '0' );
        }

        add_action('wp_insert_post', array($this, 'afcmpn_post_save'),10, 3);
    
    }

    function afcmpn_post_save($post_id, $post, $update)
    {
        if(get_option('afcmpn_api') && isset($post->post_status) && $post->post_status == 'publish') {
            if ($update) {
                if (get_post_meta( $post_id, 'send-fcm-checkbox', true )) {           
                    if ($this->get_options_posttype($post->post_type)) {
                        $result = $this->arrowdan_notifier($post);
                    } elseif ($this->get_options_posttype($post->post_type)) {
                        $result = $this->arrowdan_notifier($post);
                    }
                }
                
            }
        }
    }

    function afcmpn_meta_callback( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'afcmpn_nonce' );
        $afcmpn_stored_meta = get_post_meta( $post->ID );
        ?>
     
            <p>
                <span class="fcm-row-title"><?php echo esc_html(__( 'Check to send push notification: ', AFCMPN_T ));?></span>
                <div class="fcm-row-content">
                    <label for="send-fcm-checkbox">
                        <input type="checkbox" name="send-fcm-checkbox" id="send-fcm-checkbox" value="1" checked/>
                        <?php echo esc_attr(__( 'Send Push Notification', AFCMPN_T ));?>
                    </label>            
                </div>
            </p>   
     
        <?php
    }

    public function afcmpn_activate()
    {
    }

    public function afcmpn_deactivate()
    {
    }
}
// This class is created to test user apiKey is valid or not before sending actual notification to application.
class ARROWDANTestNotifier{
    public  $ID;
    public  $post_title;
    public  $post_content;
    public  $post_excerpt;
}
$ARROWDAN_FCM_PN_OBJ = new ARROWDAN_FCM_PN();