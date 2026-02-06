<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * This page serve as dashboard template
 */
// do not render this page if its found outside of main class
if ( ! isset( $this->main_menu_slug ) ) {
	return false;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Function is prefixed with 'ccew_'
$ccew_is_active             = false;
$ccew_classes               = 'plugin-block';
$ccew_is_installed          = false;
$ccew_button                = null;
$ccew_available_version     = null;
$ccew_update_available      = false;
$ccew_update_stats          = '';
$ccew_pro_already_installed = false;
 //var_dump($this->disable_plugins);
// Let's see if a pro version is already installed
if ( isset( $this->disable_plugins[ $plugin_slug ] ) ) {
	$ccew_pro_version = $this->disable_plugins[ $plugin_slug ];
	if ( file_exists( WP_PLUGIN_DIR . '/' . $ccew_pro_version['pro'] ) ) {
		$ccew_pro_already_installed = true;
		$ccew_classes              .= ' plugin-not-required';
	}
}

if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {

	$ccew_is_installed      = true;
	$ccew_plugin_file       = null;
	$ccew_installed_plugins = get_plugins(); // get_option('active_plugins', false);
	$ccew_is_active         = false;
	$ccew_classes          .= ' installed-plugin';

	foreach ( $ccew_installed_plugins as $plugin => $ccew_data ) {
		$ccew_thisPlugin = substr( $plugin, 0, strpos( $plugin, '/' ) );
		if ( strcasecmp( $ccew_thisPlugin, $plugin_slug ) == 0 ) {

			if ( isset( $ccew_plugin_version ) && version_compare( $ccew_plugin_version, $ccew_data['Version'] ) > 0 ) {
				$ccew_available_version = $ccew_plugin_version;
				$ccew_plugin_version    = $ccew_data['Version'];
				$ccew_update_stats      = '<span class="plugin-update-available">Update Available: v ' . $ccew_available_version . '</span>';
			}

			if ( is_plugin_active( $plugin ) ) {
				$ccew_is_active = true;
				$ccew_classes  .= ' active-plugin';
				break;
			} else {
				$ccew_plugin_file = $plugin;
				$ccew_classes    .= ' inactive-plugin';
			}
		}
	}
	if ( $ccew_is_active ) {
		$ccew_button = '<button class="button button-disabled">Active</button>';
	} else {
		$ccew_wp_nonce = wp_create_nonce( 'cp-nonce-activate-' . $plugin_slug );
		$ccew_button  .= '<button class="button activate-now cool-plugins-addon plugin-activator" data-plugin-tag="' . esc_attr( $tag ) . '" data-plugin-id="' . esc_attr( $ccew_plugin_file ) . '"
        data-action-nonce="' . esc_attr( $ccew_wp_nonce ) . '" data-plugin-slug="' . esc_attr( $plugin_slug ) . '">Activate</button>';
	}
} else {
	$ccew_wp_nonce = wp_create_nonce( 'cp-nonce-download-' . $plugin_slug );
	$ccew_classes .= ' available-plugin';
	if ( $plugin_url != null ) {
		$ccew_button = '<button class="button install-now cool-plugins-addon plugin-downloader" data-plugin-tag="' . esc_attr( $tag ) . '"  data-action-nonce="' . esc_attr( $ccew_wp_nonce ) . '" data-plugin-slug="' . esc_attr( $plugin_slug ) . '">Install</button>';
	} elseif ( isset( $plugin_pro_url ) ) {
		$ccew_button = '<a class="button install-now cool-plugins-addon pro-plugin-downloader" href="' . esc_url( $plugin_pro_url ) . '" target="_new">Buy Pro</a>';
	}
}

// Remove install / activate button if pro version is already installed
if ( $ccew_pro_already_installed === true ) {
	$ccew_pro_ver = $this->disable_plugins[ $plugin_slug ];
	$ccew_button  = '<button class="button button-disabled" title="This plugin is no more required as you already have ' . $ccew_pro_ver['pro'] . '">Pro Installed</button>';
}

// All php condition formation is over here
?>



<div class="<?php echo esc_attr($ccew_classes); ?>">
  <div class="plugin-block-inner">

	<div class="plugin-logo">
	<img src="<?php echo esc_url($plugin_logo); ?>" width="250px" />
	</div>

	<div class="plugin-info">
	  <h4 class="plugin-title"> <?php echo esc_html($plugin_name); ?></h4>
	  <div class="plugin-desc"><?php echo wp_kses_post($plugin_desc); ?></div>
	  <div class="plugin-stats">
	  <?php echo wp_kses_post($ccew_button); ?>
	  <?php if ( isset( $ccew_plugin_version ) && ! empty( $ccew_plugin_version ) ) : ?>
		<div class="plugin-version">v <?php echo esc_html($ccew_plugin_version); ?></div>
			<?php echo wp_kses_post($ccew_update_stats); ?>
	  <?php endif; ?>
	  </div>
	</div>

  </div>
</div>
