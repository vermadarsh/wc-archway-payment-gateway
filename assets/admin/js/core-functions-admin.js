/**
 * jQuery admin custom script file.
 */
jQuery( document ).ready( function( $ ) {
	'use strict';

	// Localized variables.
	var ajaxurl = CF_Admin_JS_Obj.ajaxurl;

	/**
	 * Get the transaction details.
	 */
	$( document ).on( 'click', '.cf-view-transaction-details', function() {
		var order_id  = $( '#post_ID' ).val();
		var this_link = $( this );

		// Block element.
		block_element( this_link );

		// Send the AJAX.
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'get_transaction',
				order_id: order_id,
			},
			success: function ( response ) {
				// Return, if the response is not proper.
				if ( 0 === response ) {
					console.log( 'archway-payment: invalid ajax call' );
					return false;
				}

				if ( 'cf-transaction-details-fetched' === response.data.code ) {
					// Unblock the element.
					unblock_element( this_link );

					// Paste the HTML.
					$( '.cf-transaction-details' ).html( response.data.html );
				}
			},
		} );
	} );

	/**
	 * Block element.
	 *
	 * @param {string} element
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}

	/**
	 * Check if a number is valid.
	 * 
	 * @param {number} data 
	 */
	function is_valid_number( data ) {
		if ( '' === data || undefined === data || isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * Check if a string is valid.
	 *
	 * @param {string} $data
	 */
	function is_valid_string( data ) {
		if ( '' === data || undefined === data || ! isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}
} );
