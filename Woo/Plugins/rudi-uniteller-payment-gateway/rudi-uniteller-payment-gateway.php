<?php

/*
Plugin Name: RuDi Uniteller Payment Gateway
Plugin URI: 
Description: Accept credit card payments from Uniteller Gateway in your WooCommerce store
Author: Goodrudi
Version: 1.0
Author URI: http://violand.ru
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}
// Add the gateway to WC Available Gateways
function add_uniteller_gateway( $methods ) {
	$methods[] = 'WC_Uniteller_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_uniteller_gateway' );

// Uniteller Payment Gateway
add_action( 'plugins_loaded', 'wc_uniteller_gateway_init', 11 );

function wc_uniteller_gateway_init() {

	class WC_Uniteller_Gateway extends WC_Payment_Gateway {
		
		public function __construct() {
			
			$this->id 					= 'uniteller';
            $this->has_fields 			= false;
			$this->method_title 		= 'Uniteller_Payments';
			$this->method_description 	= 'Accept payments with Credit Cards via Uniteller processing company.';

            $this->init_form_fields();
            $this->init_settings();

            // user setting variables
            $this->enabled 				= $this->get_option('enabled');
			$this->title 				= $this->get_option('title');
			$this->description 			= $this->get_option('description');
			$this->instructions 		= $this->get_option( 'instructions', $this->description );
			$this->shop_id 				= $this->get_option('shop_id');
			$this->login 				= $this->get_option('login');
            $this->password 			= $this->get_option('password');
			$this->latin_name 			= $this->get_option('latin_name');
			$this->url_return_page 		= $this->get_option('url_return_page');
			$this->liveurl				= "https://wpay.uniteller.ru/pay/";
			$this->icon 				= plugins_url( 'logo_PS.png' , __FILE__ );
			
			//add woocommerce payment option
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_email_customer_details', array( $this, 'payment_button' ), 30, 3 );
		}
		
		//Initialize Gateway Settings Form Fields
		public function init_form_fields() {
			
			$this->form_fields = array(
				'enabled'      => array(
                  'title'        => __('Enable/Disable', 'woocommerce'),
                  'type'         => 'checkbox',
                  'label'        => __('Включить оплату по картам', 'woocommerce'),
                  'default'      => 'no'
				),
            
				'title'        => array(
                  'title'        => __('Заголовок:', 'woocommerce'),
                  'type'         => 'text',
                  'description'  => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                  'default'      => __('Uniteller Payments', 'woocommerce'),
				  'desc_tip'    => true,
				),
            
				'description'  => array(
                  'title'        => __('Описание:', 'woocommerce'),
                  'type'         => 'text',
                  'description'  => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                  'default'      => __('Pay by Credit or Debit Cards', 'woocommerce'),
				  'desc_tip'    => true,
				),
            
				'shop_id'     => array(
                  'title'        => __('Shop ID:', 'woocommerce'),
                  'type'         => 'text',
                  'description'  => __('Идентификатор точки продаж Uniteller Point ID', 'woocommerce'),
				  'desc_tip'    => true,
				),
            
				'login' => array(
                  'title'        => __('Логин:', 'woocommerce'),
                  'type'         => 'text',
                  'description'  =>  __('Логин, указанный в Личном кабинете Uniteller', 'woocommerce'),
				  'desc_tip'    => true,
				),
            
				'password' => array(
                  'title'        => __('Пароль:', 'woocommerce'),
                  'type'         => 'password',
                  'description'  =>  __('Пароль, указанный в Личном кабинете Uniteller', 'woocommerce'),
				  'desc_tip'    => true,
				),
			
				'latin_name'        => array(
                  'title'        => __('Название магазина:', 'woocommerce'),
                  'type'         => 'text',
                  'description'  => __('Название магазина на латинице, указанное в Личном кабинете Uniteller', 'woocommerce'),
                  'default'      => '',
				  'desc_tip'    => true,
				),
			
				'url_return_page' => array(
                  'title'        => __('Адрес страницы возврата:', 'woocommerce'),
                  'type'         => 'text',
                  'description'=>  __('Страница магазина, на которую должен вернуться покупатель после оплаты', 'woocommerce'),
                  'default'      => '',
				  'desc_tip'    => true,
				),
			
				'instructions' => array(
					'title'       => __( 'Инструкции:', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Инструкции покупателям как оплатить', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true,
				)
		  	);
		}
		
		//Output for the order received page
		public function thankyou_page($order) {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
			echo '<p>'.__('Пожалуйста, нажмите кнопку "Оплатить". Вы будете перенаправлены на страницу платежной системы.', 'woocommerce').'</p>';
			echo $this->generate_uniteller_form($order);
		}
		
		//Output for the pay button in the new order emails
		public function payment_button($order) {
			echo '<hr>';
			echo '<p style="color:#c30075;"><strong>Для оплаты этого заказа нажмите кнопку "Оплатить".</strong></p>';
			echo '<div><small>Вы будете перенаправлены на защищенную страницу платежной системы «Uniteller», где сможете оплатить заказ с помощью Вашей банковской карты.<br>
			Компания «Uniteller» - крупнейшая компания-эквайер, которая предоставляет услуги по приему платежей через Интернет.</small></div>';
			echo $this->generate_uniteller_form($order);
		}
		
		//Process the payment and return the result
		public function process_payment( $order_id ) {
			$order = new WC_Order( $order_id );
			
			// Mark as on-hold (waiting for the payment)
			$order->update_status( 'on-hold', __( 'Waiting for the payment', 'woocommerce' ) );
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
		
		//Prepare Uniteller signature
		public function getSignature( $Shop_IDP , $Order_ID , $Subtotal_P , $password) {
	
			$MeanType = $EMoneyType = $Lifetime = $Customer_IDP = $Card_IDP = $IData = $PT_Code = '';
			$Signature = strtoupper(
				md5(
				md5($Shop_IDP) . '&' .
				md5($Order_ID) . '&' .
				md5($Subtotal_P) . '&' .
				md5($MeanType) . '&' .
				md5($EMoneyType) . '&' .
				md5($Lifetime) . '&' .
				md5($Customer_IDP) . '&' .
				md5($Card_IDP) . '&' .
				md5($IData) . '&' .
				md5($PT_Code) . '&' .
				md5($password)
				)
			);
			return $Signature;
		}
		
		//Uniteller link button
		public function generate_uniteller_form($order_id) {
			
			global $woocommerce;
            $order = new WC_Order($order_id);
			
			$Shop_IDP = $this->shop_id;
			$Order_ID = $order->get_order_number();
			$Subtotal_P = $order->get_total();
			$URL_RETURN = $this->url_return_page;
			$password = $this->password;

			$Signature = $this->getSignature( $Shop_IDP , $Order_ID , $Subtotal_P , $password);
		
			echo '<form action= "' . esc_url( $this->liveurl ) . '" method= "POST" >
			<input type= "hidden" name= "Shop_IDP" value= "' . $Shop_IDP . '" />
			<input type= "hidden" name= "Order_IDP" value= "' . $Order_ID . '"/>
			<input type= "hidden" name= "Subtotal_P" value= "' . $Subtotal_P . '"/>
			<input type= "hidden" name= "Signature" value= "' . $Signature . '"/>
			<input type= "hidden" name= "URL_RETURN" value= "' . $URL_RETURN . '"/>
			<input type= "submit" class= "button wc-forward" name= "Submit" value= "Оплатить"/>
			</form >';
		}
	}
}		