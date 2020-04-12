<?php
define( 'OPTIONS_SLUG', 'virtue_premium' );
define( 'LANGUAGE_SLUG', 'virtue' );
load_theme_textdomain('virtue', get_template_directory() . '/languages');
/*
 * Init Theme Options
 */
require_once locate_template('/themeoptions/framework.php');          		// Options framework
require_once locate_template('/themeoptions/options.php');          		// Options framework
require_once locate_template('/themeoptions/options/virtue_extension.php'); // Options framework extension
require_once locate_template('/kt_framework/extensions.php');        		// Remove options from the admin

/*
 * Init Theme Startup/Core utilities
 */
require_once locate_template('/lib/utils.php');           		            // Utility functions
require_once locate_template('/lib/init.php');            					// Initial theme setup and constants
require_once locate_template('/lib/sidebar.php');         					// Sidebar class
require_once locate_template('/lib/config.php');          					// Configuration
require_once locate_template('/lib/cleanup.php');        					// Cleanup
require_once locate_template('/lib/custom-nav.php');        				// Nav Options
require_once locate_template('/lib/nav.php');            					// Custom nav modifications
require_once locate_template('/lib/metaboxes.php');     					// Custom metaboxes
require_once locate_template('/lib/gallery_metabox.php');     				// Custom Gallery metaboxes
require_once locate_template('/lib/taxonomy-meta-class.php');   			// Taxonomy meta boxes
require_once locate_template('/lib/taxonomy-meta.php');         			// Taxonomy meta boxes
require_once locate_template('/lib/comments.php');        					// Custom comments modifications
require_once locate_template('/lib/post-types.php');      					// Post Types
require_once locate_template('/lib/Mobile_Detect.php');        				// Mobile Detect
require_once locate_template('/lib/aq_resizer.php');      					// Resize on the fly
require_once locate_template('/lib/revslider-activate.php');   				// Plugin Activation

/*
 * Init Shortcodes
 */
require_once locate_template('/lib/kad_shortcodes/shortcodes.php');      					// Shortcodes
require_once locate_template('/lib/kad_shortcodes/carousel_shortcodes.php');   				// Carousel Shortcodes
require_once locate_template('/lib/kad_shortcodes/custom_carousel_shortcodes.php');   		// Carousel Shortcodes
require_once locate_template('/lib/kad_shortcodes/testimonial_shortcodes.php');   			// Carousel Shortcodes
require_once locate_template('/lib/kad_shortcodes/testimonial_form_shortcode.php');   		// Carousel Shortcodes
require_once locate_template('/lib/kad_shortcodes/blog_shortcodes.php');   					// Blog Shortcodes
require_once locate_template('/lib/kad_shortcodes/image_menu_shortcodes.php'); 				// image menu Shortcodes
require_once locate_template('/lib/kad_shortcodes/google_map_shortcode.php');  				// Map Shortcodes
require_once locate_template('/lib/kad_shortcodes/portfolio_shortcodes.php'); 				// Portfolio Shortcodes
require_once locate_template('/lib/kad_shortcodes/portfolio_type_shortcodes.php'); 			// Portfolio Shortcodes
require_once locate_template('/lib/kad_shortcodes/staff_shortcodes.php'); 					// Staff Shortcodes
require_once locate_template('/lib/kad_shortcodes/gallery.php');      						// Gallery Shortcode

/*
 * Init Widgets
 */
require_once locate_template('/lib/premium_widgets.php'); 					// Gallery Widget
require_once locate_template('/lib/widgets.php');         					// Sidebars and widgets

/*
 * Template Hooks
 */
require_once locate_template('/lib/custom.php');          					// Custom functions
require_once locate_template('/lib/authorbox.php');         				// Author box
require_once locate_template('/lib/breadcrumbs.php');         				// Breadcrumbs
require_once locate_template('/lib/template_hooks.php'); 					// Template Hooks
require_once locate_template('/lib/custom-woocommerce.php'); 				// Woocommerce functions

/*
 * Load Scripts
 */
require_once locate_template('/lib/admin_scripts.php');    					// Icon functions
require_once locate_template('/lib/scripts.php');        					// Scripts and stylesheets
require_once locate_template('/lib/custom_css.php'); 						// Fontend Custom CSS

/*
 * Updater
 */
require_once locate_template('/lib/wp-updates-theme.php');
new WPUpdatesThemeUpdater_647( 'http://wp-updates.com/api/2/theme', basename( get_template_directory() ) );

/*
 * Admin Shortcode Btn
 */
function virtue_shortcode_init() {
	if(is_admin()){ if(kad_is_edit_page()){require_once locate_template('/lib/kad_shortcodes.php');	}}
}
add_action('init', 'virtue_shortcode_init');

/*
 * Change Name of Emails
 */
function change_name($name) {
	return 'Магазин "Школа лепки"';
}

add_filter('wp_mail_from_name','change_name');

function change_email($email) {
	return 'zakaz@shkola-lepki.ru';
}
 
add_filter('wp_mail_from','change_email');

/*
 * Change number of products per page
 */

add_filter( 'loop_shop_per_page', function ( $cols ) {
    // $cols contains the current number of products per page based on the value stored on Options -> Reading
    // Return the number of products you wanna show per page.
    return 100;
}, 20 );

// Cheapest Price

add_filter( 'woocommerce_variable_sale_price_html', 'wc_wc20_variation_price_format', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wc_wc20_variation_price_format', 10, 2 );

function wc_wc20_variation_price_format( $price, $product ) {
    // Main Price
    $prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
    $price = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );

    // Sale Price
    $prices = array( $product->get_variation_regular_price( 'min', true ), $product->get_variation_regular_price( 'max', true ) );
    sort( $prices );
    $saleprice = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );

    if ( $price !== $saleprice ) {
        $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
    }

    return $price;
}

/*
 * Minimum order ammount
 */
add_action( 'woocommerce_checkout_process', 'wc_minimum_order_amount' );
add_action( 'woocommerce_before_cart' , 'wc_minimum_order_amount' );
 
function wc_minimum_order_amount() {
    // Set this variable to specify a minimum order value
    $minimum = 10;
 
    if ( WC()->cart->subtotal < $minimum ) {
 
        if( is_cart() ) {
 
            wc_print_notice( 
                sprintf( 'Внимание! Для оформления заказа, общая стоимость товаров в вашей корзине должна быть не меньше %s без учета стоимости доставки, сейчас общая сумма %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->subtotal )
                ), 'error' 
            );
 
        } else {
 
            wc_add_notice( 
                sprintf( 'Внимание! Для оформления заказа, общая стоимость товаров в вашей корзине должна быть не меньше %s без учета стоимости доставки, сейчас общая сумма %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->subtotal )
                ), 'error' 
            );
 
        }
    }
 
}

// Adds customer first and last name to admin new order email subject
function skyverge_add_customer_to_email_subject( $subject, $order ) {

	$subject .= ' : ' . $order->billing_last_name . ' ' . $order->billing_first_name;
	return $subject;

}
add_filter( 'woocommerce_email_subject_new_order', 'skyverge_add_customer_to_email_subject', 10, 2 );

//remove comments tab

add_filter( 'woocommerce_product_tabs', 'rudi_remove_comments_tab', 95);

function rudi_remove_comments_tab( $tabs ) {
	unset ($tabs['reviews']);
	return $tabs;
}
//add comments on the description tab

add_action( 'woocommerce_after_single_product_summary', 'comments_template', 12 );

// preformatted shipping address in emails

add_filter('woocommerce_localisation_address_formats', 'rudi_address_formats');

function rudi_address_formats( $formats ) {
    $formats[ 'default' ] =  "{country}\n{postcode}\n{state}{city}\n{address_1}\n{address_2}\n{company}\n{last_name} {first_name}";   
    return $formats;
}

// change remove_item_from_the_cart sign

function rudi_remove_cart_item_sign($sprintf, $cart_item_key) {
	
	global $woocommerce;
	$sprintf = str_replace("&times;", "удалить", $sprintf);
	return $sprintf;
}
add_filter( 'woocommerce_cart_item_remove_link', 'rudi_remove_cart_item_sign',10, 2);

//reverse details fields in customer emails

add_filter( 'woocommerce_email_customer_details_fields', 'rudi_reverse_details_fields' );

function rudi_reverse_details_fields( $fields ) {
	$fields = array_reverse ($fields);
	return ($fields);
}

//reverse "customer_details" and "email_addresses" blocks in emails

add_action( 'woocommerce_email', 'rudi_email_action_remove' ); 
	function rudi_email_action_remove( $email_class ) {
    remove_action( 'woocommerce_email_customer_details', array( $email_class, 'customer_details' ), 10 );
}
add_action( 'woocommerce_email_customer_details', array( 'WC_Emails', 'customer_details' ), 30 );

//remove header of "customer_details" block in emails

add_filter( 'woocommerce_email_custom_details_header', 'rudi_unset_header');

function rudi_unset_header( $heading) {
	$heading = '';
	return $heading;
}

//remove customer details labels in emails

add_filter('woocommerce_email_customer_details_fields', 'rudi_unset_labels' );
function rudi_unset_labels( $fields) {
	$fields['billing_email']['label'] = '';
	$fields['billing_phone']['label'] = '';
	return $fields;
}

//make uppercase shipping address

function rudi_address_replace_array($replacement, $args) {
		extract($args);
		$replacement['{address_1}'] = mb_strtoupper( $address_1 );
		$replacement['{address_2}'] = mb_strtoupper( $address_2 );
		$replacement['{city}'] = mb_strtoupper( $city );
		$replacement['{postcode}'] = mb_strtoupper( $postcode );
		
		return $replacement;
}
add_filter( 'woocommerce_formatted_address_replacements', 'rudi_address_replace_array', 10, 2 );

//remove meta data from single product page

remove_action ('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

//add "sales" text on single product page

add_action ('woocommerce_after_add_to_cart_form', 'skidki_text');

function skidki_text() {
echo '<div class="skidki2"><p>Вы можете оплатить заказ на нашем сайте он-лайн с помощью банковской карты</p><img src="/wp-content/uploads/2019/02/logoPS051-e1551123233293.png"></div>';
}


//add "order-again" button on the cart page
add_action ('woocommerce_after_cart_table', 'add_order_again_button');
function add_order_again_button () {
echo '<p class="order-again">
	<a href="http://demo.shkola-lepki.ru" class="button">Продолжить покупки</a>
</p>';
}

//adding messages in the cart about coupons

add_action ('woocommerce_after_cart_table', 'add_coupon_message');

function add_coupon_message () {
 if (empty( WC()->cart->get_applied_coupons() )) {
  $subt = WC()->cart->subtotal;    
  if ( $subt >= 3000 && $subt < 5000 ) {
     echo '<div class="cart-info-skidka">Уважаемый покупатель, Вам доступна скидка 3 % за заказ на сумму свыше 3000 руб!<br>
Для активации скидки введите код <b>333</b> в поле "Код купона" выше и нажмите кнопку "Применить купон".<br>Стоимость заказа будет автоматически пересчитана с учетом скидки.<br><br>Применить купон к данному заказу можно только один раз.</div>';
} elseif ( $subt >= 5000 && $subt < 10000 ) {
     echo '<div class="cart-info-skidka">Уважаемый покупатель, Вам доступна скидка 5 % за заказ на сумму свыше 5000 руб!<br>
Для активации скидки введите код <b>555</b> в поле "Код купона" выше и нажмите кнопку "Применить купон".<br>Стоимость заказа будет автоматически пересчитана с учетом скидки.<br><br>Применить купон к данному заказу можно только один раз.</div>';
} elseif ( $subt >= 10000 && $subt < 30000 ) {
     echo '<div class="cart-info-skidka">Уважаемый покупатель, Вам доступна скидка 10 % за заказ на сумму свыше 10000 руб!<br>
Для активации скидки введите код <b>1010</b> в поле "Код купона" выше и нажмите кнопку "Применить купон".<br>Стоимость заказа будет автоматически пересчитана с учетом скидки.<br><br>Применить купон к данному заказу можно только один раз.</div>';
} elseif ( $subt >= 30000 ) {
    echo '<div class="cart-info-skidka">Уважаемый покупатель, Вам доступна скидка 20 % за заказ на сумму свыше 30000 руб!<br>
Для активации скидки введите код <b>2020</b> в поле "Код купона" выше и нажмите кнопку "Применить купон".<br>Стоимость заказа будет автоматически пересчитана с учетом скидки.<br><br>Применить купон к данному заказу можно только один раз.</div>';
}
}
}

add_filter('woocommerce_get_order_item_totals', 'coupon_additional_text', 10, 2);

function coupon_additional_text( $total_rows, $order ) {
	global $wpdb;
	$codes = $order->get_used_coupons();
    if (!empty($codes)) {
	$coupon_row = '';
    foreach ($codes as $code) {
	$coupon_row .= $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish';", $code )) . '. ';
	}
	$total_rows['discount']['label'] = __( 'Discount:', 'woocommerce' ).'   '.$coupon_row;
  }
	return $total_rows;
}
//
//add "stock status" catalog orderby

add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_catalog_ordering_args' );

function custom_catalog_ordering_args( $args ) {
	$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

	if ( 'stock_status' == $orderby_value ) {
		$args['orderby'] = 'meta_value title';
		$args['order'] = 'ASC';
		$args['meta_key'] = '_stock_status';
	}
	return $args;
}

add_filter( 'woocommerce_default_catalog_orderby_options', 'stock_status_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'stock_status_orderby' );

function stock_status_orderby( $catalog_orderby_options ) {
	$orderby_option['stock_status'] = 'По наличию на складе';
	return $orderby_option + $catalog_orderby_options;
}

/*
add_filter( 'woocommerce_email_recipient_new_order', 'add_another_recipient', 10, 2 );

function add_another_recipient( $recipient, $order ) {
	
	if ( ! $order instanceof WC_Order ) {
		return $recipient; 
	}
	
	$ship_method = $order->get_shipping_method();
	if ( $ship_method === 'Самовывоз со склада' || $ship_method === 'Курьерская доставка по Москве' ) {
		$recipient = $recipient . ',goodrudi@yahoo.com,info@shkola-lepki.ru';
	}
	return $recipient;
}
*/

/*
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

add_action( 'woocommerce_single_product_summary', 'rudi_text_instead_add_to_cart', 30 );

function rudi_text_instead_add_to_cart() {
echo '<div class="stop-coronavirus"><p>Не доступно для заказа. Сидим дома!</p></div>';
}
*/
