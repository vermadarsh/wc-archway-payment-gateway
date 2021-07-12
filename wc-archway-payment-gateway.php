<?php
/**
 * The plugin bootstrap file.
 *
 * @link              https://github.com/vermadarsh/
 * @since             1.0.0
 * @package           Core_Functions
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Archway Payment Gateway
 * Plugin URI:        https://github.com/vermadarsh/wc-archway-payment-gateway/
 * Description:       This plugin adds Archway Payment Gateway to your WooCommerce store.
 * Version:           1.0.0
 * Author:            Adarsh Verma
 * Author URI:        https://github.com/vermadarsh/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-archway-payment-gateway
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version. Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CF_PLUGIN_VERSION', '1.0.0' );

/**
 * Define the constants.
 */
$uploads_dir = wp_upload_dir();
$cons        = array(
	'CF_PLUGIN_PATH'  => plugin_dir_path( __FILE__ ),
	'CF_PLUGIN_URL'   => plugin_dir_url( __FILE__ ),
	'CF_LOG_DIR_URL'  => $uploads_dir['baseurl'] . '/wc-logs/',
	'CF_LOG_DIR_PATH' => $uploads_dir['basedir'] . '/wc-logs/',
);
foreach ( $cons as $con => $value ) {
	define( $con, $value );
}

/**
 * This code runs during the plugin activation.
 * This code is documented in includes/class-cf-core-functions-activator.php
 */
function activate_core_functions() {
	require 'includes/class-cf-core-functions-activator.php';
	Cf_Core_Functions_Activator::run();
}

register_activation_hook( __FILE__, 'activate_core_functions' );

/**
 * This code runs during the plugin deactivation.
 * This code is documented in includes/class-cf-core-functions-deactivator.php
 */
function deactivate_core_functions() {
	require 'includes/class-cf-core-functions-deactivator.php';
	Cf_Core_Functions_Deactivator::run();
}

register_deactivation_hook( __FILE__, 'deactivate_core_functions' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_core_funcitons() {
	require_once 'includes/cf-core-functions.php';

	// The core plugin class that is used to define internationalization and admin-specific hooks.
	require_once 'includes/class-cf-core-functions-admin.php';
	new Cf_Core_Functions_Admin();

	// The core plugin class that is used to define internationalization and public-specific hooks.
	require_once 'includes/class-cf-core-functions-public.php';
	new Cf_Core_Functions_Public();

	// The core plugin class that is used to define internationalization and public-specific hooks.
	require_once 'includes/class-woocommerce-archway-payment-gateway.php';
	new WooCommerce_Archway_Payment_Gateway();
}

/**
 * This initiates the plugin.
 * Checks for the required plugins to be installed and active.
 */
function cf_plugins_loaded_callback() {
	$active_plugins = get_option( 'active_plugins' );
	$is_wc_active   = in_array( 'woocommerce/woocommerce.php', $active_plugins, true );

	if ( false === $is_wc_active ) {
		add_action( 'admin_notices', 'cf_admin_notices_callback' );
	} else {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cf_plugin_actions_callback' );
		run_core_funcitons();
	}
}

add_action( 'plugins_loaded', 'cf_plugins_loaded_callback' );

/**
 * This function is called to show admin notices for any required plugin not active || installed.
 */
function cf_admin_notices_callback() {
	$this_plugin_data = get_plugin_data( __FILE__ );
	$this_plugin      = $this_plugin_data['Name'];
	$wc_plugin        = __( 'WooCommerce', 'wc-archway-payment-gateway' );
	?>
	<div class="error">
		<p>
			<?php /* translators: 1: %s: string tag open, 2: %s: strong tag close, 3: %s: this plugin, 4: %s: woocommerce plugin */ ?>
			<?php echo wp_kses_post( sprintf( __( '%1$s%3$s%2$s is ineffective as it requires %1$s%4$s%2$s to be installed and active.', 'wc-archway-payment-gateway' ), '<strong>', '</strong>', $this_plugin, $wc_plugin ) ); ?>
		</p>
	</div>
	<?php
}

/**
 * This function adds custom plugin actions.
 *
 * @param array $links Links array.
 * @return array
 */
function cf_plugin_actions_callback( $links ) {
	$this_plugin_links = array(
		'<a title="' . __( 'Settings', 'wc-archway-payment-gateway' ) . '" href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=archway_payments' ) ) . '">' . __( 'Settings', 'wc-archway-payment-gateway' ) . '</a>',
	);

	return array_merge( $this_plugin_links, $links );
}

/**
 * Debugger function which shall be removed in production.
 */
if ( ! function_exists( 'debug' ) ) {
	/**
	 * Debug function definition.
	 *
	 * @param string $params Holds the variable name.
	 */
	function debug( $params ) {
		echo '<pre>';
		print_r( $params );
		echo '</pre>';
	}
}
