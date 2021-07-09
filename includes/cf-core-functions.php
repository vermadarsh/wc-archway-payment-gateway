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
