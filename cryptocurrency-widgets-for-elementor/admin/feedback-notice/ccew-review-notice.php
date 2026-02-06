<?php

if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists( 'ccew_review_notice' ) ) {
	class ccew_review_notice {
		/**
		 * The Constructor
		 */
		public function __construct() {
			// register actions
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'ccew_admin_notice_for_reviews' ) );
				add_action( 'wp_ajax_ccew_dismiss_notice', array( $this, 'ccew_dismiss_review_notice' ) );
			}
		}

		/**
		 * Ajax callback for dismissing the review notice
		 */
		public function ccew_dismiss_review_notice() {
			// Check for nonce security
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(wp_unslash($_POST['nonce']), 'ccew-nonce' ) ) {
				wp_send_json_error( 'You don\'t have permission to hide notice.' );
				return;
			}
			
			// Verify user capability to dismiss admin notices
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You don\'t have permission to dismiss admin notices.' );
				return;
			}
			update_option( 'ccew-alreadyRated', 'yes' );
			wp_send_json_success();
		}

		/**
		 * Display the admin notice for reviews
		 */
		public function ccew_admin_notice_for_reviews() {
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			// Check if the user has already rated the plugin
			$already_rated = get_option( 'ccew-alreadyRated', 'no' );
			if ( $already_rated === 'yes' ) {
				return;
			}

			// Get installation date and compare it with the current date
			$installation_date = get_option( 'ccew_activation_time' );
			if ( is_numeric( $installation_date ) ) {
				$installation_date = gmdate( 'Y-m-d h:i:s', $installation_date );
			}

			$install_date = new DateTime( $installation_date );
			$current_date = new DateTime();
			$diff         = $install_date->diff( $current_date );
			
			$diff_days    = $diff->days;

			// Check if installation days is greater than or equal to 3
			if ( $diff_days >= 3) {
				wp_enqueue_script( 'ccew-feedback-notice-script', CCEW_URL . 'admin/feedback-notice/js/ccew-admin-feedback-notice.js', array( 'jquery' ), CCEW_VERSION, true );
				wp_enqueue_style( 'ccew-feedback-notice-styles', CCEW_URL . 'admin/feedback-notice/css/ccew-admin-feedback-notice.css', array(), CCEW_VERSION );
				echo wp_kses_post( $this->ccew_create_notice_content() );
			}
		}

		/**
		 * Generate the HTML content for the review notice
		 */
		public function ccew_create_notice_content() {
			$ajax_url           = esc_url(admin_url('admin-ajax.php'));
			$ajax_callback      = esc_attr('ccew_dismiss_notice');
			$wrap_cls           = esc_attr('notice notice-info is-dismissible');
			$p_name             = esc_html__('Cryptocurrency Widgets For Elementor', 'cryptocurrency-widgets-for-elementor');
			$like_it_text       = esc_html__('Rate Now! ★★★★★', 'cryptocurrency-widgets-for-elementor');
			$already_rated_text = esc_html__('Already Reviewed', 'cryptocurrency-widgets-for-elementor');
			$not_like_it_text   = esc_html__('No, not good enough, I do not like to rate it!', 'cryptocurrency-widgets-for-elementor');
			$not_interested     = esc_html__('Not Interested', 'cryptocurrency-widgets-for-elementor');
			$p_link             = esc_url( 'https://wordpress.org/support/plugin/cryptocurrency-widgets-for-elementor/reviews/#new-post' );

			$nonce   = wp_create_nonce( 'ccew-nonce' );
			$message = sprintf(
				'Thanks for using <b>%s</b> WordPress plugin. We hope you liked it !<br/>Please give us a quick rating, it works as a boost for us to keep working on more <a href="https://coolplugins.net" target="_blank"><strong>Cool Plugins</strong></a>!',
				$p_name
			);

			$html = '<div data-ajax-url="%s" data-ajax-callback="%s" 
			data-nonce="%s" 
			class="cool-feedback-notice-wrapper %s">
        <div class="message_container">%s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%s" class="like_it_btn button button-primary" target="_new" title="%s">%s</a></li>
            <li class="already_rated"><a href="#" class="already_rated_btn button ccew_dismiss_notice" title="%s">%s</a></li>
            <li class="already_rated"><a href="#" class="already_rated_btn button ccew_dismiss_notice" title="%s">%s</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';

			return sprintf(
				$html,
				$ajax_url,
				$ajax_callback,
				$nonce,
				$wrap_cls,
				$message,
				$p_link,
				$like_it_text,
				$like_it_text,
				$already_rated_text,
				$already_rated_text,
				$not_interested,
				$not_interested
			);
		}
	} //class end
}
