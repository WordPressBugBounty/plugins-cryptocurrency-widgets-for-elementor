<?php
/**
 * Plugin Name:Cryptocurrency Widgets For Elementor
 * Description:Cryptocurrency Widgets For Elementor WordPress plugin displays current prices of crypto coins - bitcoin, ethereum, ripple etc.
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard_pro
 * Plugin URI:https://cryptocurrencyplugins.com/
 * Version: 1.7.4
 * License: GPL2
 * Text Domain:ccew
 * Domain Path: languages
 *
 * Elementor tested up to:3.29.0
 * Elementor Pro tested up to: 3.29.0
 * */

if (!defined('ABSPATH')) {
    exit;
}

define('CCEW_VERSION', '1.7.4');
define('CCEW_FILE', __FILE__);
define('CCEW_DIR', plugin_dir_path(CCEW_FILE));
define('CCEW_URL', plugin_dir_url(CCEW_FILE));

if (!defined('CCEW_DEMO_URL')) {
    define('CCEW_DEMO_URL', 'https://cryptocurrencyplugins.com');
    define('CCEW_UTM', '?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=demo&utm_content=ccw_widget_settings');
    define('CCEW_CMC_UTM', '?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=demo&utm_content=cmc_widget_settings');

}
define('CCEW_FEEDBACK_API',"https://feedback.coolplugins.net/");


/**
 * Class Crypto_Currency_Elementor_Widget
 */
final class Crypto_Currency_Elementor_Widget
{

    /**
     * Plugin instance.
     *
     * @var Crypto_Currency_Elementor_Widget
     * @access private
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return Crypto_Currency_Elementor_Widget
     * @static
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @access private
     */

    private function __construct()
    {
        // register activation deactivation hooks
        register_activation_hook(CCEW_FILE, array($this, 'ccew_activate'));
        register_deactivation_hook(CCEW_FILE, array($this, 'ccew_deactivate'));

        // Required file included
        $this->ccew_file_include();

        // Insert Data
        add_action('init', array($this, 'ccew_data_insert'));

        // ajax call for coin data
        add_action('wp_ajax_ccew_getData', 'ccew_getData');
        add_action('wp_ajax_nopriv_ccew_getData', 'ccew_getData');

        // Load the plugin after Elementor  are loaded.
        add_action('plugins_loaded', array($this, 'ccew_plugins_loaded'));
        add_action('init', array($this, 'ccew_verify_plugin_version'));
        add_action('init', array($this, 'reset_option_data_once_on_first_of_month'));
        add_action('init', array($this, 'ccew_load_textdomain'));
        if (is_admin()) {

            add_action('admin_enqueue_scripts', array($this, 'ccew_load_scripts'));
        }


    }


    public function ccew_plugins_loaded()
    {
        // Notice if the Elementor is not active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'ccew_fail_to_load'));
            return;
        }

    }
    function ccew_cron_job_init(){
    
      
        $options        = get_option('openexchange-api-settings', []);
 
        if ( isset( $options['ccew_extra_info'] ) && ( ! empty( $options['ccew_extra_info'] ) || $options['ccew_extra_info'] === 'on' ) ) {
 
        if (!wp_next_scheduled('ccew_extra_data_update')) {

           wp_schedule_event(time(), 'every_30_days', 'ccew_extra_data_update');

       }
      }
    }
    public function ccew_fail_to_load()
    {
        if (!is_plugin_active('elementor/elementor.php')): ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php echo '<a href="https://wordpress.org/plugins/elementor/"  target="_blank" >' . esc_html__('Elementor Page Builder', 'ccew') . '</a>' . wp_kses_post(__(' must be installed and activated for "<strong>Cryptocurrency Elementor Widgets</strong>" to work', 'ccew')); ?></p>
			</div>
			<?php
endif;

    }
    public function ccew_load_textdomain()
    {
        load_plugin_textdomain('ccew', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    public function ccew_file_include()
    {
        if (is_admin()) {
            // require_once(CCEW_DIR . '/admin/cmb2/init.php');
            require_once CCEW_DIR . '/admin/addon-dashboard-page/addon-dashboard-page.php';
            cool_plugins_crypto_addon_settings_page('crypto', 'cool-crypto-plugins', 'Cryptocurrency plugins by CoolPlugins.net', 'Crypto Plugins', 'dashicons-chart-area');
            require_once CCEW_DIR . '/admin/openexchange-api-settings.php';
            require_once CCEW_DIR . '/admin/settings.php';

            if(!class_exists('CPFM_Feedback_Notice')){
               require_once CCEW_DIR . '/admin/feedback/cpfm-common-notice.php';
            }

            add_action('cpfm_register_notice', function () {
            
                if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
                    return;
                }

                $notice = [

                    'title' => __('Cryptocurrency Plugins by Cool Plugins', 'ccpw'),
                    'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'cool-plugins-feedback'),
                    'pages' => ['cool-crypto-plugins', 'ccpw_get_started','openexchange-api-settings'],
                    'always_show_on' => ['cool-crypto-plugins','ccpw_get_started','openexchange-api-settings'], // This enables auto-show
                    'plugin_name'=>'ccew'
                ];

                CPFM_Feedback_Notice::cpfm_register_notice('crypto', $notice);

                    if (!isset($GLOBALS['cool_plugins_feedback'])) {
                        $GLOBALS['cool_plugins_feedback'] = [];
                    }
                
                    $GLOBALS['cool_plugins_feedback']['crypto'][] = $notice;
           
            });
         
            add_action('cpfm_after_opt_in_ccew', function($category) {

                if ($category === 'crypto') {

                    CCEW_cronjob::ccew_send_data();
                    
                    $options = get_option('openexchange-api-settings', []);
                    $options['ccew_extra_info'] = true;
                    update_option('openexchange-api-settings', $options);
                }
            });
        }
        
        require CCEW_DIR . 'includes/cron/ccew-class-cron.php';
        require CCEW_DIR . 'includes/ccew-elementor-register.php';
        require CCEW_DIR . 'includes/ccew-widget-functions.php';
        require CCEW_DIR . 'includes/ccew-ajaxhandler.php';
        require CCEW_DIR . 'includes/ccew-crypto-elementor-db.php';

        // Review File
        require_once CCEW_DIR . '/admin/ccew-review-notice.php';
        new ccew_review_notice();

        // if( is_admin() === true ){
        require_once CCEW_DIR . 'admin/feedback/admin-feedback-form.php';
        // }
        
    }

    public function ccew_data_insert()
    {
        $check_data = get_option('ccew_data_save');

        if ($check_data != 'true') {
            $api = get_option('ccew-api-settings');
            $api = (!isset($api['select_api']) && empty($api['select_api'])) ? 'coin_gecko' : sanitize_text_field($api['select_api']);

            $data = ($api == 'coin_gecko') ? ccew_widget_insert_data() : ccew_widget_coin_peprika_insert_data();

            update_option('ccew_data_save', 'true');

        }

    }

    /*
    |--------------------------------------------------------------------------
    |  Check if plugin is just updated from older version to new
    |--------------------------------------------------------------------------
     */
    public function ccew_verify_plugin_version()
    {
        $CCEW_VERSION = get_option('CCEW_FREE_VERSION');

        if (isset($CCPW_VERSION) && version_compare($CCPW_VERSION, CCEW_VERSION, '<')) {

            $this->ccew_activate();
            delete_option('ccew-fresh-installation');
            $conversions = get_transient('cmc_usd_conversions');
            if (!empty($conversions)) {
                update_option('cmc_usd_conversions', $conversions);
            }

        }

        // Check if the transient exists and has expired
        $transient_exists = get_transient('ccew_check_coin_list_after_24hour');

        if (!$transient_exists) {
            // calling function which updates data
            $obj = new ccew_database();
            $obj->ccew_check_coin_list();

            // Set transient to schedule the next execution after 24 hours
            set_transient('ccew_check_coin_list_after_24hour', true, 24 * HOUR_IN_SECONDS);
        }

    } // end of cmc_plugin_version_verify()

    public function ccew_activate()
    {
        $DB = new ccew_database();
        $DB->create_table();
        update_option('ccew-v', CCEW_VERSION);
        update_option('CCEW_FREE_VERSION', CCEW_VERSION);
        update_option('ccew_activation_time', gmdate('Y-m-d h:i:s'));
        update_option('ccew_data_save', 'false');
        update_option('ccew-alreadyRated', 'no');
        update_option('ccew-fresh-installation', 'new user');
        
        if (!get_option( 'ccew_initial_save_version' ) ) {
            add_option( 'ccew_initial_save_version', CCEW_VERSION );
        }


        if(!get_option( 'ccew-install-date' ) ) {
            add_option( 'ccew-install-date', gmdate('Y-m-d h:i:s') );
        }
        $this->ccew_cron_job_init();
    }


    public function ccew_deactivate()
    {
        $db = new ccew_database();
        $db->drop_table();
        delete_transient('ccew-saved-coindata');
        delete_option('ccew_data_save');
        if (wp_next_scheduled('ccew_extra_data_update')) {
            wp_clear_scheduled_hook('ccew_extra_data_update');
        }

    }
    
    public function ccew_on_widgets_registered()
    {
        $this->ccew_widget_includes();
    }
    public function reset_option_data_once_on_first_of_month()
    {
        // Check if it's the 1st day of the month
        $current_date = date('j');

        if ($current_date === '1') {
            // Check if a flag or option indicating the reset has already been performed
            $reset_flag = get_option('ccpw_reset_flag');

            // If the reset has not been performed (reset_flag is not set), perform the reset
            if (empty($reset_flag)) {
                // Reset your option data
                update_option('cmc_coingecko_api_hits', 0);

                // Set a flag to indicate that the reset has been performed
                update_option('ccpw_reset_flag', '1');
            }
        } else {
            if (get_option('ccpw_reset_flag')) {
                delete_option('ccpw_reset_flag');
            }
        }
    }
    


    public function ccew_load_scripts($hook)
    {

        
        if(!wp_script_is( 'cpfm-data-share-setting.js' )){
            $screen = get_current_screen();   
            if (strpos($screen->id, 'openexchange-api-settings') !== false) {
                wp_enqueue_script('cpfm-settings-data-share', CCEW_URL . 'assets/js/cpfm-data-share-setting.js', array('jquery'), CCEW_VERSION, true);
            }
        }
       
    }

}
function Crypto_Currency_Elementor_Widget()
{
    return Crypto_Currency_Elementor_Widget::get_instance();
}

Crypto_Currency_Elementor_Widget();
