<?php
/**
 * The file that defines the archway payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with ArchWay.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions
 * @subpackage Core_Functions/includes
 */

/**
 * The file that defines the archway payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with ArchWay.
 *
 * @since      1.0.0
 * @package    Core_Functions
 * @author     Adarsh Verma <adarsh@cmsminds.com>
 */
class WooCommerce_Archway_Payment_Gateway extends WC_Payment_Gateway {
    public $domain;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/common-function.php';
        $this->domain = 'archway_payment_gateway';

        $this->id                 = 'archway_payments';
        $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
        $this->has_fields         = false;
        $this->method_title       = __( 'Archway Payments', $this->domain );
        $this->method_description = __( 'Allows payments with custom gateway.', $this->domain );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->order_status = $this->get_option( 'order_status', 'completed' );
        $this->api_url      = $this->get_option( 'api_url' );
        $this->api_key      = $this->get_option( 'api_key' );

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', $this->domain ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Custom Payment', $this->domain ),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __( 'Title', $this->domain ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
                'default'     => __( 'Archway Payments', $this->domain ),
                'desc_tip'    => true,
            ),
            'order_status' => array(
                'title'       => __( 'Order Status', $this->domain ),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
                'default'     => 'wc-completed',
                'desc_tip'    => true,
                'options'     => wc_get_order_statuses()
            ),
            'description' => array(
                'title'       => __( 'Description', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
                'default'     => __('Payment Information', $this->domain),
                'desc_tip'    => true,
            ),
            'instructions' => array(
                'title'       => __( 'Instructions', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'api_url' => array(
                'title'       => __( 'API URL', $this->domain ),
                'type'        => 'text',
                'description' => __( 'This controls the api url which pass to parameters on archway payment gateway.', $this->domain ),
                'default'     => __( 'API URL', $this->domain ),
                'desc_tip'    => true,
            ),
            'api_key' => array(
                'title'       => __( 'API KEY', $this->domain ),
                'type'        => 'password',
                'description' => __( 'This controls the api key which pass to archway payment gateway API.', $this->domain ),
                'default'     => __( 'API KEY', $this->domain ),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions )
            echo wpautop( wptexturize( $this->instructions ) );
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && 'archway_payments' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }
    /*
    * Function to display Front end Credit card form.
    */
    public function payment_fields(){

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }

        ?>
        <div id="custom_input">
          <div class="form-group" id="card-number-field">
              <label for="cardNumber">Card Number</label>
              <input type="text" name="cardNumber" class="form-control" id="cardNumber" maxlength="19" placeholder-"1234-1234-1234-1234">
          </div>
            <div class="form-group CVV">
                <label for="cvv">CVV</label>
                <input type="password" name="cvv" class="form-control" id="cvv" maxlength="3"  placeholder-"CVV">
            </div><br/>
            <!-- <div class="form-group expiration-date">
                <label for="expirationDate">Expiration (mm/yy)</label>
                <input type="text" name="expirationDate" class="form-control" id="expirationDate" maxlength="5">
            </div> -->
            <div class="form-group" id="expiration-date">
                <label>Expiration</label>
                <?php
                $months = cpg_get_months(); ?>
                <select name="expiry_month" id="expiry_month">
                  <?php foreach($months as $key=> $single_month) {
                    echo '<option value="'. $key .'">'. $single_month .'</option>';
                  } ?>

                </select>
                <select name="expiry_year" id="expiry_year">
                  <?php
                  $this_year = date("Y"); // Run this only once
                  for ($year = $this_year; $year <= $this_year + 20; $year++) {
                    echo '<option value="'. $year .'">'.$year.'</option>';
                  } ?>


                </select>
            </div><br/>
            <div class="form-group owner">
              <label for="owner">Card Holder Name</label>
              <input type="text" name="owner" class="form-control" id="owner">
            </div>
            <!-- <div class="form-group" id="credit_cards">
                <img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/visa.svg" id="visa">
                <img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/mastercard.svg" id="mastercard">
                <img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/amex.svg" id="amex">
            </div> -->
        </div>
        <?php
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

        // Set order status
        $order->update_status( $status, __( 'Checkout with custom payment. ', $this->domain ) );

        // or call the Payment complete
        // $order->payment_complete();

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url( $order )
        );
    }
}
