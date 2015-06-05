<?php

class WC_Redsys_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		
		$this->id				= 'redsys';
		$this->icon 			= apply_filters('woocommerce_redsys_icon', plugin_dir_url( __FILE__ ) . '/tarjetas.png');
		$this->has_fields 		= false;
		$this->method_title     = __( 'RedSys/Servired', "redsys_gw_woo" );

		$this->init_form_fields();
		$this->init_settings();

		$this->title 			= apply_filters( 'wooredsys_title', $this->get_option( 'title' ) );
		$this->description      = apply_filters( 'wooredsys_description', $this->get_option( 'description' ) );
		
		$this->commerce 		= $this->get_option( 'commerce' );
		$this->terminal 		= $this->get_option( 'terminal' );
		$this->key 				= $this->get_option( 'key' );
		$this->url				= $this->get_option( 'url' );
		$this->signature 		= $this->get_option( 'signature' );
		$this->test 			= $this->get_option( 'test' );
		$this->merchantName 	= $this->get_option( 'merchantName' );
		$this->owner 			= $this->get_option( 'owner' );
		$this->after_payment	= $this->get_option( 'after_payment' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_redsys', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( &$this, 'redsys_ipn_response') );
	}

	function redsys_ipn_response(){
		$post_filtered = filter_input_array( INPUT_POST );
		
		if ( $post_filtered['Ds_Response'] == '0000' ):
			$order_id = substr( $post_filtered['Ds_Order'], 0, 8 );		
			$order = new WC_Order( $order_id );

			if ( $order->status == 'completed' )
				exit;

			$virtual_order = null;
 
			if ( count( $order->get_items() ) > 0 ) {
				foreach( $order->get_items() as $item ) {
					if ( 'line_item' == $item['type'] ) {
						$_product = $order->get_product_from_item( $item );

						if ( ! $_product->is_virtual() ) {
							$virtual_order = false;
							break;
						} else {
							$virtual_order = true;
						}	
					}
				}
			}

			$downloadable_order = null;
 
			if ( count( $order->get_items() ) > 0 ) {
				foreach( $order->get_items() as $item ) {
					if ( 'line_item' == $item['type'] ) {
						$_product = $order->get_product_from_item( $item );

						if ( ! $_product->is_virtual() ) {
							$downloadable_order = false;
							break;
						} else {
							$downloadable_order = true;
						}	
					}
				}
			}

			// choose between options
			switch( $this->after_payment ){
				case "completed":
					$order->update_status( 'completed' );
				break;

				case "processing":
					$order->update_status( 'processing' );
				break;

				case "completed_downloadable":
					if( $downloadable_order )
						$order->update_status( 'completed' );
					else
						$order->update_status( 'processing' );
				break;

				case "completed_virtual":
					if( $virtual_order )
						$order->update_status( 'completed' );
					else
						$order->update_status( 'processing' );
				break;

				case "completed_downloadable_virtual":
					if( $downloadable_order || $virtual_order )
						$order->update_status( 'completed' );
					else
						$order->update_status( 'processing' );
				break;
			}

			$order->add_order_note( sprintf( __( 'RedSys/Servired order completed, code %s', "redsys_gw_woo" ), $post_filtered['Ds_AuthorisationCode'] ) );
		else:
			$order = new WC_Order( $post_filtered['Ds_Order'] );

			$order->update_status('cancelled');
			
			$order->add_order_note( sprintf( __( 'RedSys/Servired payment error, code %s', "redsys_gw_woo" ), $post_filtered['Ds_ErrorCode'] ) );	
		endif;
	}
	
	function init_form_fields() {

		$this->form_fields = array(
				'enabled' => array(
						'title' => __( 'Enable/Disable', "redsys_gw_woo" ),
						'type' => 'checkbox',
						'label' => __( 'Enable RedSys/Servired', "redsys_gw_woo" ),
						'default' => 'yes'
				),
				'title' => array(
						'title' => __( 'Title', "redsys_gw_woo" ),
						'type' => 'text',
						'description' => __( 'This title is showed in checkout process.', "redsys_gw_woo" ),
						'default' => __( 'RedSys/Servired', "redsys_gw_woo" ),
						'desc_tip'      => true,
				),
				'description' => array(
						'title' => __( 'Description', "redsys_gw_woo" ),
						'type' => 'textarea',
						'description' => __( 'Description of the method of payment. Use it to tell the user that it is a secure system through bank.', "redsys_gw_woo" ),
						'default' => __( 'Secure payment by credit card. You will be redirected to the secure website of the bank.', "redsys_gw_woo" )
				),
				'owner' => array(
						'title' => __( 'Owner', "redsys_gw_woo" ),
						'type' => 'text',
						'default' => ''
				),
				'merchantName' => array(
						'title' => __( 'Trade name', "redsys_gw_woo" ),
						'type' => 'text',
						'default' => ''
				),
				'commerce' => array(
						'title' => __( 'Trade number', "redsys_gw_woo" ),
						'type' => 'text',
						'default' => ''
				),
				'terminal' => array(
						'title' => __( 'Terminal number', "redsys_gw_woo" ),
						'type' => 'text',
						'default' => '1'
				),
				'key' => array(
						'title' => __( 'Secret key', "redsys_gw_woo" ),
						'type' => 'text',
						'description' => __('Encryptation Secret Key.', "redsys_gw_woo" ),
						'default' => ''
				),
				'signature' => array(
						'title' => __( 'Sort Code', "redsys_gw_woo" ),
						'type'	=> 'select',
						'options' => array(
								'complete' => __( 'Complete', "redsys_gw_woo" ),
								'extended' => __( 'SHA1 - Complete Extended', "redsys_gw_woo" )
						),
						'description' => '',
						'default' => 'extended'
				),
				'url' => array(
						'title' => __( 'Url', "redsys_gw_woo" ),
						'type'	=> 'select',
						'options' => array(
								'sermepa' => __( 'Sermepa', "redsys_gw_woo" ),
								'redsys' => __( 'RedSys', "redsys_gw_woo" )
						),
						'description' => '',
						'default' => 'redsys'
				),
				'test' => array(
						'title' => __( 'Test Mode', "redsys_gw_woo" ),
						'type' => 'checkbox',
						'label' => __( 'Enable RedSys/Servired test mode.', "redsys_gw_woo" ),
						'default' => 'yes'
				),
				'after_payment' => array(
						'title' => __( 'What to do after payment is done?', "redsys_gw_woo" ),
						'type'	=> 'select',
						'options' => array(
								'processing' => __( 'Always processing', "redsys_gw_woo" ),
								'completed_downloadable' => __( 'Pending except if all products are downloadable, in this case it would be marked as completed', "redsys_gw_woo" ),
								'completed_virtual' => __( 'Pending except if all products are virtual, in this case it would be marked as completed', "redsys_gw_woo" ),
								'completed_downloadable_virtual' => __( 'Pending except if all products are downloadable or virtual, in this case it would be marked as completed', "redsys_gw_woo" ),
								'completed' => __( 'Always completed', "redsys_gw_woo" )
						),
						'description' => __( 'After payment, how the order should be marked?', "redsys_gw_woo" ),
				),
		);
	}

	public function admin_options() {
		?>
		<h3><?php _e( 'RedSys/Servired Payment', "redsys_gw_woo" ); ?></h3>
		<p><?php _e('Allows RedSys/Servired card payments.', "redsys_gw_woo" ); ?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>

		<h3><?php _e( 'How it works? (video in Spanish)', "redsys_gw_woo" ); ?></h3>
		<div id="redsys_video_explicativo" style="width:853px; margin:0 auto;">
			<iframe width="853" height="480" src="https://www.youtube.com/embed/tFz7m9ls3XU?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
		</div>

		<script>
		jQuery( document ).ready( function( $ ){
			$.validator.addMethod("requiredIfChecked", function (val, ele, arg) {
			    if ($("#woocommerce_redsys_enabled").is(":checked") && ($.trim(val) == '')) { return false; }
			    return true;
			}, "This field is required if gateway is enabled");


			$("#mainform").validate({
				rules: {
					woocommerce_redsys_title: "requiredIfChecked",
					woocommerce_redsys_description: "requiredIfChecked",
					woocommerce_redsys_owner: "requiredIfChecked",
					woocommerce_redsys_merchantName: "requiredIfChecked",
					woocommerce_redsys_commerce: "requiredIfChecked",
					woocommerce_redsys_terminal: "requiredIfChecked",
					woocommerce_redsys_key: "requiredIfChecked"
				},

				messages: {
					woocommerce_redsys_title: "<?php _e( 'You must fill out a title for this gateway', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_description: "<?php _e( 'You must fill out a description for this gateway', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_owner: "<?php _e( 'You must fill out who is the owner', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_merchantName: "<?php _e( 'You must fill out the merchant name', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_commerce: "<?php _e( 'You must fill out the merchant number', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_terminal: "<?php _e( 'You must fill out the terminal number. If you don not know it, it probably would be the number: 1', 'redsys_gw_woo' ); ?>",
					woocommerce_redsys_key: "<?php _e( 'You must fill out the key', 'redsys_gw_woo' ); ?>"
				}
			});			
		} )
		</script>
		<?php
	}

	function receipt_page( $order ) {
		echo '<p>'.__( 'Thank you for your order, click on the button to pay with RedSys/Servired.', "redsys_gw_woo" ).'</p>';
		echo $this->generate_redsys_form( $order );
	}

	function generate_redsys_form( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );

		if ( $this->test == 'yes' ) {
			$gateway_address = 'https://sis-t.sermepa.es:25443/sis/realizarPago';
			if ( $this->url == "redsys" ) {
				$gateway_address = 'https://sis-t.redsys.es:25443/sis/realizarPago';
			}
		} else {
			$gateway_address = 'https://sis.sermepa.es/sis/realizarPago';
			if ( $this->url == "redsys" ) {
				$gateway_address = 'https://sis.redsys.es/sis/realizarPago';
			}
		}

		$servired_args = $this->prepare_args( $order );

		$servired_args_array = array();

		foreach ($servired_args as $key => $value) {
			$servired_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
		}

		wc_enqueue_js( '
			jQuery("body").block({
				message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__( 'Thank you for your order. We are now redirecting you to Redsys/Servired to make payment.', "redsys_gw_woo" ).'",
				overlayCSS:
				{
					background: "#fff",
					opacity: 0.6
				},
				css: {
					padding:        20,
					textAlign:      "center",
					color:          "#555",
					border:         "3px solid #aaa",
					backgroundColor:"#fff",
					cursor:         "wait",
					lineHeight:		"32px"
				}
			});
			jQuery("#submit_redsys_payment_form").click();
		' );
	 	
		return '<form action="'.esc_url( $gateway_address ).'" method="post" id="redsys_payment_form" target="_top">
			' . implode( '', $servired_args_array) . '
			<input type="submit" class="button-alt" id="submit_redsys_payment_form" value="'.__( 'Pay via Redsys/Servired', "redsys_gw_woo" ).'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__( 'Cancel order &amp; restore cart', "redsys_gw_woo" ).'</a>
			</form>';

	}

	function prepare_args( $order ) {
		global $woocommerce;

		$order_id = $order->id;
		$ds_order = str_pad($order->id, 8, "0", STR_PAD_LEFT) . date('is');
		
		if ($this->signature == "complete"):
			$message =  $order->get_total()*100 .
			$ds_order .
			$this->commerce .
			"978" .
			$this->key;
				
			$signature = strtoupper(sha1($message));
		else:
			$message =  $order->get_total()*100 .
			$ds_order .
			$this->commerce .
			"978" .
			"0" .
			add_query_arg( 'wc-api', 'WC_Redsys_Gateway', home_url( '/' ) ) .
			$this->key;
				
			$signature = strtoupper(sha1($message));
		endif;
		
		$args = array (
				'Ds_Merchant_MerchantCode'			=> $this->commerce,
				'Ds_Merchant_Terminal'				=> $this->terminal,
				'Ds_Merchant_Currency'				=> 978,
				'Ds_Merchant_MerchantURL'			=> add_query_arg( 'wc-api', 'WC_Redsys_Gateway', home_url( '/' ) ),
				'Ds_Merchant_TransactionType'		=> 0,
				'Ds_Merchant_MerchantSignature'		=> $signature,
				'Ds_Merchant_UrlKO'					=> apply_filters( 'wooredsys_param_urlKO', get_permalink( woocommerce_get_page_id( 'checkout' ) ) ),
				'Ds_Merchant_UrlOK'					=> apply_filters( 'wooredsys_param_urlOK', $this->get_return_url( $order ) ),
				'Ds_Merchant_Titular'				=> $this->owner,
				'Ds_Merchant_MerchantName'			=> $this->merchantName,
				'Ds_Merchant_Amount'				=> round($order->get_total()*100),
				'Ds_Merchant_ProductDescription'	=> sprintf( __( 'Order %s' , "redsys_gw_woo" ), $order->get_order_number() ),
				'Ds_Merchant_Order'					=> $ds_order,
			
		);	
			
		return $args;		
	}

	function process_payment( $order_id ) {

    	$order = new WC_Order( $order_id );

    	return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
		);
    }

}