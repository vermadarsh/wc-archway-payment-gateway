<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since   1.0.0
 * @package Sync_Grants
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'cf_get_posts' ) ) {
	/**
	 * Get the posts.
	 *
	 * @param string $post_type Post type.
	 * @param int    $paged Paged value.
	 * @param int    $posts_per_page Posts per page.
	 * @return object
	 * @since 1.0.0
	 */
	function cf_get_posts( $post_type = 'post', $paged = 1, $posts_per_page = -1 ) {
		// Prepare the arguments array.
		$args = array(
			'post_type'      => $post_type,
			'paged'          => $paged,
			'posts_per_page' => $posts_per_page,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		/**
		 * Posts/custom posts listing arguments filter.
		 *
		 * This filter helps to modify the arguments for retreiving posts of default/custom post types.
		 *
		 * @param array $args Holds the post arguments.
		 * @return array
		 */
		$args = apply_filters( 'cf_posts_args', $args );

		return new WP_Query( $args );
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'cf_get_months_array' ) ) {
	/**
	 * Get the months array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function cf_get_months_array() {
		// Prepare the months array.
		$months = array(
			'01' => __( 'January', 'wc-archway-payment-gateway' ),
			'02' => __( 'February', 'wc-archway-payment-gateway' ),
			'03' => __( 'March', 'wc-archway-payment-gateway' ),
			'04' => __( 'April', 'wc-archway-payment-gateway' ),
			'05' => __( 'May', 'wc-archway-payment-gateway' ),
			'06' => __( 'June', 'wc-archway-payment-gateway' ),
			'07' => __( 'July', 'wc-archway-payment-gateway' ),
			'08' => __( 'August', 'wc-archway-payment-gateway' ),
			'09' => __( 'September', 'wc-archway-payment-gateway' ),
			'10' => __( 'October', 'wc-archway-payment-gateway' ),
			'11' => __( 'November', 'wc-archway-payment-gateway' ),
			'12' => __( 'December', 'wc-archway-payment-gateway' ),
		);

		/**
		 * Months array.
		 *
		 * This filter helps to modify the months array.
		 *
		 * @param array $months Months array.
		 * @return array
		 * @since 1.0.0
		 */
		$months = apply_filters( 'cf_months_array', $months );

		return $months;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'cf_is_localhost' ) ) {
	/**
	 * Check if the user is in localhost.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	function cf_is_localhost() {
		$localhost_ip_addresses = array(
			'127.0.0.1',
			'::1',
		);

		$current_ip = $_SERVER['REMOTE_ADDR'];

		return ( in_array( $current_ip, $localhost_ip_addresses, true ) ) ? true : false;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'cf_get_transaction' ) ) {
	/**
	 * Get the archway transaction details.
	 *
	 * @param int $transaction_id Archway transaction ID.
	 * @return array|boolean
	 * @since 1.0.0
	 */
	function cf_get_transaction( $transaction_id ) {
		$gateway_settings = woocommerce_archway_payments_settings();
		$api_url          = ( ! empty( $gateway_settings['get_transaction_api_url'] ) ) ? $gateway_settings['get_transaction_api_url'] : '';
		$api_key          = ( ! empty( $gateway_settings['api_key'] ) ) ? $gateway_settings['api_key'] : '';

		// Return false, if the transaction API URL is not available.
		if ( empty( $api_url ) || empty( $api_key ) ) {
			return false;
		}

		// Fire the API now.
		$response = wp_remote_get(
			str_replace( '$transaction_id', $transaction_id, $api_url ),
			array(
				'headers' => array(
					'X-Api-Key' => $api_key,
				)
			)
		);

		// Get response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $response_code ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			return (array) $response_body;
		}

		return false;
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'cf_get_archway_payments_settings' ) ) {
	/**
	 * Get the archway payment settings.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function woocommerce_archway_payments_settings() {
		$settings = get_option( 'woocommerce_archway_payments_settings' );

		// Is sandbox mode on.
		$is_sandbox = ( ! empty( $settings['is_sandbox'] ) && 'yes' === $settings['is_sandbox'] ) ? true : false;

		// Get transaction API URL.
		$get_transaction_api_url = ( ! empty( $settings['get_transaction_api_url'] ) ) ? $settings['get_transaction_api_url'] : '';
		if ( $is_sandbox && ! empty( $get_transaction_api_url ) ) {
			$get_transaction_api_url = str_replace( 'https://api', 'https://devapi', $get_transaction_api_url );
			$get_transaction_api_url = str_replace( 'v1/api/', 'v1/test/', $get_transaction_api_url );
		}

		// Process transaction API URL.
		$process_transaction_api_url = ( ! empty( $settings['process_transaction_api_url'] ) ) ? $settings['process_transaction_api_url'] : '';
		if ( $is_sandbox && ! empty( $process_transaction_api_url ) ) {
			$process_transaction_api_url = str_replace( 'https://api', 'https://devapi', $process_transaction_api_url );
			$process_transaction_api_url = str_replace( 'v1/api/', 'v1/test/', $process_transaction_api_url );
		}

		// Api Key.
		$api_key = ( $is_sandbox ) ? $settings['sandbox_api_key'] : $settings['production_api_key'];

		// Prepare the settings array.
		return array(
			'is_sandbox'                  => $is_sandbox,
			'get_transaction_api_url'     => $get_transaction_api_url,
			'process_transaction_api_url' => $process_transaction_api_url,
			'api_key'                     => $api_key,
		);
	}
}

/**
 * Check if the function exists.
 */
if ( ! function_exists( 'scf_write_payment_log' ) ) {
	/**
	 * Write log to the log file.
	 *
	 * @param string $message Holds the log message.
	 * @return void
	 */
	function scf_write_payment_log( $message = '' ) {
		global $wp_filesystem;

		if ( empty( $message ) ) {
			return;
		}

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$local_file = CF_LOG_DIR_PATH . 'transactions-log.log';

		// If the file doesn't exist.
		if ( ! $wp_filesystem->exists( $local_file ) ) {
			return;
		}

		// Fetch the old content and add the new content.
		$content  = $wp_filesystem->get_contents( $local_file );
		$content .= "\n" . gmdate( 'Y-m-d h:i:s' ) . ' :: ' . $message;

		$wp_filesystem->put_contents(
			$local_file,
			$content,
			FS_CHMOD_FILE // predefined mode settings for WP files.
		);
	}
}
