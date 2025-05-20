<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('CCEW_cronjob')) {
    class CCEW_cronjob
    {
      

        public function __construct()
        {
            
            add_filter('cron_schedules', array($this, 'ccew_cron_schedules'));
            add_action('ccew_extra_data_update', array($this, 'ccew_cron_extra_data_autoupdater'));

        }

        public function ccew_cron_schedules($schedules)
        {
           
            if (!isset($schedules['5min'])) {
                $schedules['5min'] = array(
                    'interval' => 5 * 60,
                    'display' => __('Once every 5 minutes'),
                );
            }
            // 30days schedule for update information

            if (!isset($schedules['every_30_days'])) {

                $schedules['every_30_days'] = array(
                    'interval' => 30 * 24 * 60 * 60, // 2,592,000 seconds
                    'display'  => __('Once every 30 days'),
                );
            }
            return $schedules;
        }

        
        function ccew_cron_extra_data_autoupdater(){
            
            $settings       = get_option('openexchange-api-settings', []);
            $ccew_response  = isset($settings['ccew_extra_info']) ? $settings['ccew_extra_info'] : '';


            if (!empty($ccew_response) || $ccew_response === 'on'){
          
                if (class_exists('CCEW_cronjob')) {
                    CCEW_cronjob::ccew_send_data();
                }
            }

        }


                
        static public function ccew_send_data() {
 
                 $feedback_url = 'http://feedback.coolplugins.net/wp-json/coolplugins-feedback/v1/site';
                 require_once CCEW_DIR . 'admin/feedback/admin-feedback-form.php';
                    
                    
                 if (!defined('CCEW_DIR') || !class_exists('\ccew\feedback\cp_feedback')) {
                        return;
                 }
                    
            
                  $extra_data         = new \ccew\feedback\cp_feedback();
                  $extra_data_details = $extra_data->cpfm_get_user_info();

                  $server_info        = $extra_data_details['server_info'];
                  $extra_details      = $extra_data_details['extra_details'];
                  $site_url           = get_site_url();
                  $install_date       = get_option('ccew-install-date');
                  $uni_id      		  = '3';
			      $site_id            = $site_url . '-' . $install_date . '-' .$uni_id;
                 
                  $initial_version = get_option('ccew_initial_save_version');
                  $initial_version = is_string($initial_version) ? sanitize_text_field($initial_version) : 'N/A';
                  $plugin_version = defined('CCEW_VERSION') ? CCEW_VERSION : 'N/A';
                  $admin_email = sanitize_email(get_option('admin_email') ?: 'N/A');
              
                  $post_data = array(
                      'site_id'           => md5($site_id),
                      'plugin_version'    => $plugin_version,
                      'plugin_name'       => "Cryptocurrency Widgets For Elementor",
                      'plugin_initial'    => $initial_version,
                      'email'             => $admin_email,
                      'site_url'          => esc_url_raw($site_url),
                      'server_info'       => $server_info,
                      'extra_details'     => $extra_details,
                  );
              
                  $response = wp_remote_post($feedback_url, array(
                      'method'    => 'POST',
                      'timeout'   => 30,
                      'headers'   => array(
                          'Content-Type' => 'application/json',
                      ),
                      'body'      => wp_json_encode($post_data),
                  ));
              
                  if (is_wp_error($response)) {
                      error_log('CCEW Feedback Send Failed: ' . $response->get_error_message());
                      return;
                  }
              
                  $response_body = wp_remote_retrieve_body($response);
                  $decoded = json_decode($response_body, true);
                 
                  if (!wp_next_scheduled('ccew_extra_data_update')) {
                   wp_schedule_event(time(), 'every_30_days', 'ccew_extra_data_update');
                }
             
        }
          

    }

    $cron_init = new CCEW_cronjob();
}