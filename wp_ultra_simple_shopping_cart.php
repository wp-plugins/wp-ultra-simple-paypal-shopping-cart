<?php
/*
Plugin Name: WP Ultra simple Paypal Cart
Version: v4.3.2
Plugin URI: http://www.ultra-prod.com/?p=86
Author: Mike Castro Demaria
Author URI: http://www.ultra-prod.com
Description: WP Ultra simple Paypal Cart Plugin, ultra simply and easely add Shopping Cart in your WP using post or page ( you need to <a href="https://www.paypal.com/fr/mrb/pal=CH4PZVAK2GJAJ" target="_blank">create a PayPal account</a> and go to <a href="options-general.php?page=wp-ultra-simple-paypal-shopping-cart/wpussc-option.php">plugin configuration panel</a>.
Different features are available like PayPal sandbox test, price Variations, shipping Variations, unlimited extra variations label, interface text's personalization, CSS call for button, etc.
*/
/*
	This program is free software; you can redistribute it
	under the terms of the GNU General Public License version 2,
	as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/
if(!isset($_SESSION)) {
	session_start();
}	

if ( ! defined( 'WUSPSC_VERSION' ) )
    define( 'WUSPSC_VERSION', '4.3.2' );

if ( ! defined( 'WUSPSC_CART_URL' ) )
    define('WUSPSC_CART_URL', plugins_url('',__FILE__));

if ( ! defined( 'WUSPSC_PLUGIN_DIR' ) )
	define( 'WUSPSC_PLUGIN_DIR', plugin_dir_path(__FILE__) );
	
if ( ! defined( 'WUSPSC_PLUGIN_BASENAME' ) )
    define( 'WUSPSC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    
if ( ! defined( 'WUSPSC_PLUGIN_DIRNAME' ) )
    define( 'WUSPSC_PLUGIN_DIRNAME', dirname( WUSPSC_PLUGIN_BASENAME ) );
    
if ( ! defined( 'WUSPSC_PLUGIN_URL' ) )
    define( 'WUSPSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    
if ( ! defined( 'WUSPSC_PLUGIN_IMAGES_URL' ) )
    define( 'WUSPSC_PLUGIN_IMAGES_URL', WUSPSC_PLUGIN_URL . 'images/' );
    
if ( ! defined( 'WUSPSC_CONTENT_URL' ) )
    define( 'WUSPSC_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
    
if ( ! defined( 'WUSPSC_ADMIN_URL' ) )
    define( 'WUSPSC_ADMIN_URL', get_option( 'siteurl' ) . '/wp-admin' );
    
if ( ! defined( 'WUSPSC_CONTENT_DIR' ) )
    define( 'WUSPSC_CONTENT_DIR', ABSPATH . 'wp-content' );
    
if ( ! defined( 'WUSPSC_PLUGIN_URL' ) )
    define( 'WUSPSC_PLUGIN_URL', WUSPSC_CONTENT_URL. '/plugins' );
    
if ( ! defined( 'WUSPSC_PLUGIN_DIR' ) )
    define( 'WUSPSC_PLUGIN_DIR', WUSPSC_CONTENT_DIR . '/plugins' );


/* Require call */
require('up-function.php');
require('wpussc-function.php');
require('wpussc-option.php');
require('wpussc-widget.php');

// Reset the Cart as this is a returned customer from Paypal
if(isset($_GET["merchant_return_link"]) && !empty($_GET["merchant_return_link"])) {
	reset_wp_cart();
	header('Location: ' . get_option('cart_return_from_paypal_url'));
}

if(isset($_GET["mc_gross"])&&  $_GET["mc_gross"]> 0) {
	reset_wp_cart();
	header('Location: ' . get_option('cart_return_from_paypal_url'));
}

//Clear the cart if the customer landed on the thank you page
if(get_option('wpus_shopping_cart_reset_after_redirection_to_return_page')) {
	if(get_option('cart_return_from_paypal_url') == get_permalink($post->ID)) {
		reset_wp_cart();
	}
}

if($_POST['addcart']) {
	$domain_url = $_SERVER['SERVER_NAME'];
	$cookie_domain = str_replace("www","",$domain_url);		
	setcookie("cart_in_use","true",time()+21600,"/",$cookie_domain);  //useful to not serve cached page when using with a caching plugin   
	$products = $_SESSION['ultraSimpleCart'];
	$new = TRUE; 

 	if(!is_array($products)) { $products = array();}
	
	foreach($products as $key => $item) {
		if( $item['name'] != stripslashes($_POST['product']))	continue;
		$item['quantity'] += $_POST['quantity'];
		unset($products[$key]);
		array_push($products, $item);
		$new = FALSE; 
	}
	
	if ( $new == TRUE ) {
		$price = (!empty($_POST[$_POST['product']]))?
				$_POST[$_POST['product']]:
				$_POST['price'];
			
		$product = array(
			'name'			=> stripslashes($_POST['product']), 
			'price'			=> $price, 
			'quantity'		=> $_POST['quantity'], 
			'shipping'		=> $_POST['shipping'], 
			'cartLink'		=> $_POST['cartLink'], 
			'item_number'	=> $_POST['item_number']
		);
	
		array_push($products, $product);
	}
 
	sort($products);
	$_SESSION['ultraSimpleCart'] = $products;
	
	if(get_option('wpus_shopping_cart_auto_redirect_to_checkout_page')) {
		$checkout_url = get_option('cart_checkout_page_url');
		if(empty($checkout_url)) {
			echo "<br /><strong>".(__("Shopping Cart Configuration Error! You must specify a value in the 'Checkout Page URL' field for the automatic redirection feature to work!", "WUSPSC"))."</strong><br />";
		} else {
			$redirection_parameter = 'Location: '.$checkout_url;
			header($redirection_parameter);
			exit;
		}
	} 
} elseif($_POST['quantity']) {
	$products = $_SESSION['ultraSimpleCart'];
	foreach($products as $key => $item) {
		//if((stripslashes($item['name']) == stripslashes($_POST['product'])) && $_POST['quantity'])
		if((get_the_name(stripslashes($item['name'])) == stripslashes($_POST['product'])) && stripslashes($_POST['quantity'])) {
			$item['quantity'] = stripslashes($_POST['quantity']);
			unset($products[$key]);
			array_push($products, $item);
		} elseif((get_the_name(stripslashes($item['name'])) == stripslashes($_POST['product'])) && !$_POST['quantity'])
			unset($products[$key]);
	}
	sort($products);
	$_SESSION['ultraSimpleCart'] = $products;
} elseif($_POST['delcart']) {
	$products = $_SESSION['ultraSimpleCart'];
	foreach($products as $key => $item) {
		if($item['name'] == stripslashes($_POST['product']))
			unset($products[$key]);
	}
	$_SESSION['ultraSimpleCart'] = $products;
}

function print_wpus_shopping_cart( $step="paypal") {
	
	$emptyCartAllowDisplay = get_option('wpus_shopping_cart_empty_hide');
	/*if( $emptyCartAllowDisplay )
	{
		$output = get_the_empty_cart_content();
	}
	*/
	if(!cart_not_empty()) {
		$output = get_the_empty_cart_content();
	} 
		
	$email = get_bloginfo('admin_email');
	$use_affiliate_platform = get_option('wp_use_aff_platform');   
	$defaultCurrency = get_option('cart_payment_currency');
	$defaultSymbol = get_option('cart_currency_symbol');
	$defaultEmail = get_option('cart_paypal_email');
	$cart_validation_url = get_option('cart_validate_url');
	
	if(!empty($defaultCurrency))
		$paypal_currency = $defaultCurrency;
	else
		$paypal_currency = __("USD", "WUSPSC");
	if(!empty($defaultSymbol))
		$paypal_symbol = $defaultSymbol;
	else
		$paypal_symbol = __("$", "WUSPSC");

	if(!empty($defaultEmail))
		$email = $defaultEmail;
	 
	$decimal = '.';  
	$urls = '';
		
	$return = get_option('cart_return_from_paypal_url');
			
	if(!empty($return))
		$urls .= '<input type="hidden" name="return" value="'.$return.'" />';
	
	if($use_affiliate_platform) {
		if(function_exists('wp_aff_platform_install')) {
			$notify = WP_AFF_PLATFORM_URL.'/api/ipn_handler.php';
			//$notify = WUSPSC_CART_URL.'/paypal.php';
			$urls .= '<input type="hidden" name="notify_url" value="'.$notify.'" />';
		}
	}
	$title = get_option('wp_cart_title');
	//if(empty($title)) $title = __("Your Shopping Cart", "WUSPSC");
	
	global $plugin_dir_name;
	$output .= '<div class="shopping_cart" id="shopping_cart">';
	if(!get_option('wpus_shopping_cart_image_hide')) {		
		$output .= "<img src='".WUSPSC_CART_URL."/images/shopping_cart_icon.png' value='".(__("Cart", "WUSPSC"))."' title='".(__("Cart", "WUSPSC"))."' />";
	}
	/*if(!empty($title))
	{
		$output .= '<h2>';
		$output .= $title;  
		$output .= '</h2>';
	}*/
	
	$wp_cart_update_quantiy_text = get_option('wp_cart_update_quantiy_text');
	
	$output .= '<span id="pinfo" style="display: none; font-weight: bold; color: red;">'.$wp_cart_update_quantiy_text.'</span>';
	$output .= '<table style="width: 100%;">';	
	
	$count			= 1;
	$total_items	= 0;
	$total			= 0;
	$form			= '';

	if($_SESSION['ultraSimpleCart'] && is_array($_SESSION['ultraSimpleCart'])) {   
		
		if( get_option('wpus_shopping_cart_items_in_cart_hide') == "" ) {
			$itemsInCart = count($_SESSION['ultraSimpleCart']);
			$itemsInCartString = _n( get_option('singular_items_text'), get_option('plural_items_text'), $itemsInCart );
			
			$output .= '
			<tr id="item_in_cart">
			<th class="left" colspan="3">'.$itemsInCart." ".$itemsInCartString.'</th>
			</tr>';
		}
		
		foreach($_SESSION['ultraSimpleCart'] as $item) {
			$total += get_the_price($item['price']) * $item['quantity'];
			$item_total_shipping += get_the_price($item['shipping']) * $item['quantity'];
			$total_items +=  $item['quantity'];
		}

		if( $item_total_shipping == 0) {
			$baseShipping = get_option('cart_base_shipping_cost');
			$postage_cost = $item_total_shipping + $baseShipping;
		} else {
			//$postage_cost = 0;
			$postage_cost = $item_total_shipping;
		}
		
		$cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');
		if(!empty($cart_free_shipping_threshold) && $total > $cart_free_shipping_threshold) {
			$postage_cost = 0;
		}
		
		$output .= '
		<tr class="cart_labels">
			<th class="left">'.get_option('item_name_text').'</th>
			<th class="center">'.get_option('qualtity_text').'</th>
			<th class="center">'.get_option('price_text').'</th>
		</tr>';
		
		
		foreach($_SESSION['ultraSimpleCart'] as $item) {
		
			$price = get_the_price( $item['price'] );
			$name = get_the_name( $item['name'] );
			
			$wpus_display_link_in_cart = get_option('wpus_display_link_in_cart');
			
			if(!empty( $wpus_display_link_in_cart )) { 
				$cartProductDisplayLink = '<a href="'.$item['cartLink'].'">'.$name.'</a>'; 
			} else { 
				$cartProductDisplayLink = $name; 
			}
			
			$output_name .= "<input type=\"hidden\" name=\"product\" value=\"".$name."\" />";
			
			$output .= "
			<tr>
				<td class=\"cartLink\">{$cartProductDisplayLink}</td>
				<td class=\"center\">
					<form method=\"post\"  action=\"\" name='pcquantity' style='display: inline'>
					".$output_name."
					<input type=\"hidden\" name=\"cquantity\" value=\"1\" />
					<input type=\"text\" name=\"quantity\" value=\"".$item['quantity']."\" size=\"1\"  onchange=\"this.form.submit();\" onkeypress='document.getElementById(\"pinfo\").style.display = \"\";' />
					</form>
				</td>
				<td class=\"center\">".print_payment_currency(($price * $item['quantity']), $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td>
				<td>
					<form method=\"post\"  action=\"\">
					<input type=\"hidden\" name=\"product\" value=\"".$item['name']."\" />
					<input type='hidden' name='delcart' value='1' />
					<input type='image' src='".WUSPSC_CART_URL."/images/Shoppingcart_delete.png' value='".get_option('remove_text')."' title='".get_option('remove_text')."' />
					</form>
				</td>
			</tr>
			";
			
			$form .= "
				<input type=\"hidden\" name=\"item_name_$count\" value=\"".$name."\" />
				<input type=\"hidden\" name=\"amount_$count\" value='".$price."' />
				<input type=\"hidden\" name=\"quantity_$count\" value=\"".$item['quantity']."\" />
				<input type='hidden' name='item_number' value='".$item['item_number']."' />
			";	 
			$count++;
		}

		if(!get_option('wpus_shopping_cart_use_profile_shipping')) {
			$postage_cost = number_format($postage_cost,2);
			$form .= "<input type=\"hidden\" name=\"shipping_1\" value='".$postage_cost."' />";  
		}

		if(get_option('wpus_shopping_cart_collect_address')) {//force address collection
			$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"2\" />";  
		}				
	}
	
   	$count--;
   	
   	if($count) {

		if($postage_cost != 0) {
			$output .= "
			<tr>
				<td colspan=\"2\" class=\"subcell\">".get_option('subtotal_text').": </td>
				<td class=\"center\">".print_payment_currency($total, $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td><td></td></tr>
			<tr>
				<td colspan=\"2\" class=\"shipcell\">".get_option('shipping_text').": </td>
				<td class=\"center\">".print_payment_currency($postage_cost, $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td><td></td></tr>";
		}

		$output .= "
   		<tr>
   			<td colspan=\"2\" class=\"totalcel\">".get_option('total_text').": </td>
   			<td class=\"center\">".print_payment_currency(($total+$postage_cost), $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td>
   			<td></td>
   		</tr>
   		<tr>
   			<td colspan=\"4\">";
   		
   		switch($step) {
   			case "validate":
   				$output .= '<form action="'.$cart_validation_url.'" method="post">'.$form;
				if($count)
					$output .= '<input type="submit" class="step_sub button-primary" name="validate" value="'.(__("Proceed to Checkout &raquo;", "WUSPSC")).'" />';
				$output .= '</form>';
   				break;
   				
   			case "paypal":
   				// https://www.sandbox.paypal.com/cgi-bin/webscr (paypal testing site)
				// https://www.paypal.com/us/cgi-bin/webscr (paypal live site )
   				$is_sandbox = (get_option('is_sandbox') == "1")? "sandbox.": "";
   				
   				$add_cartstyle = get_option('add_cartstyle');
				if(empty($add_cartstyle)) $add_cartstyle = "wp_cart_xpcheckout_button";
   			
			  	$output .= "<form action=\"https://www.".$is_sandbox."paypal.com/cgi-bin/webscr\" method=\"post\">$form";
				if($count)
					$language = __UP_detect_language();
				//$output .= '<input type="image" src="'.WUSPSC_CART_URL.'/images/'.(__("paypal_checkout_EN.png", "WUSPSC")).'" name="submit" class="wp_cart_checkout_button" alt="'.(__("Make payments with PayPal - it\'s fast, free and secure!", "WUSPSC")).'" />';
				if(get_option('custom_paypal_button') == "1") {
					$output .= '<button name="submit" value="'.(__("Checkout", "WUSPSC")).'" title="'.(__("Checkout", "WUSPSC")).'" class="'.$add_cartstyle.'" alt="'.(__("Make payments with PayPal - it\'s fast, free and secure!", "WUSPSC")).'" />';
				} else {
					$output .= '<input type="image" src="'.WUSPSC_CART_URL.'/images/btn_xpressCheckout-'.$language.'.gif" name="submit" class="'.$add_cartstyle.'" alt="'.(__("Make payments with PayPal - it\'s fast, free and secure!", "WUSPSC")).'" />';
				} 
   			
				$output .= $urls.'
				<input type="hidden" name="business" value="'.$email.'" />
				<input type="hidden" name="currency_code" value="'.$paypal_currency.'" />
				<input type="hidden" name="cmd" value="_cart" />
				<input type="hidden" name="upload" value="1" />
				<input type="hidden" name="rm" value="2" />
				<input type="hidden" name="mrb" value="DKBDRZGU62JYC" />';
				if($use_affiliate_platform) {
					$output .= wp_cart_add_custom_field();
				}
				$output .= '</form>';
				break;
		}
   	}

   	$output .= "	   
   	</td></tr>
	</table></div>
	";

	return $output;
}

function print_wp_cart_action($content)
{
		//wp_cart_add_read_form_javascript();

		if(get_option('display_product_inline') && get_option('display_product_inline') == 1) { 
			$option_break = ' ';
		} else {
			$option_break = '<br/>';
		}
		
		$addcart = get_option('addToCartButtonName');	
		if(!$addcart || ($addcart == '') )
			$addcart = __("Add to Cart", "WUSPSC");
				
		$pattern = '#\[wp_cart:.+:price:.+:end]#';
		preg_match_all ($pattern, $content, $matches);

		foreach($matches[0] as $match) {   

			$replacement = '';
			$var_output  = '';
			$pos = strpos($match,":var1");
			
			/*
			/ free variation combo
			*/
			$isVariation = strpos($match,":var");
			if($isVariation > 0) {
				$match_tmp = $match;
				
				$pattern = '#var.*\[.*]:#';
				preg_match_all ($pattern, $match_tmp, $matchesVar);

				$allVariationArray = explode(":", $matchesVar[0][0]);
				
				for ($i=0; $i<sizeof($allVariationArray) - 1; $i++) {		

					preg_match('/(?P<vname>\w+)\[([^\)]*)\].*/', $allVariationArray[$i], $variationMatches);
					
					$allVariationLabelArray = explode("|", $variationMatches[2]);
					$variation_name = $allVariationLabelArray[0];
					
					$var_output .= '<label class="lv-label '.__UP_strtolower_utf8($variation_name).'">'.$variation_name.' :</label>';
					$variationNameValue = $i + 1;
					
					$var_output .= '<select class="sv-select variation'.$variationNameValue.'" name="variation'.$variationNameValue.'" onchange="ReadForm (this.form, false);">';
					for ($v=1; $v<sizeof( $allVariationLabelArray ); $v++) {
						$var_output .= '<option value="'.$allVariationLabelArray[$v].'">'.$allVariationLabelArray[$v].'</option>';
					}
					$var_output .= '</select>'.$option_break;

				}
			}
			
			$pattern = '[wp_cart:';	$m = str_replace ($pattern, '', $match);
			$pattern = 'price:';	$m = str_replace ($pattern, '', $m);
			$pattern = 'shipping:';	$m = str_replace ($pattern, '', $m);
			$pattern = ':end]';		$m = str_replace ($pattern, '', $m);

			$pieces = explode(':',$m);

			if(get_option('display_product_name') && get_option('display_product_name') == 1) { 
			
				$product_name = '<span class="product_name">';
				$product_name .= $pieces['0'];
				if(get_option('display_product_inline') == 1) {
					$product_name .= " :";
				}
				$product_name .= '</span>';
				
			} else {
				$product_name = '';
			}
			
			$replacement .= $product_name;

			$replacement .= '<form method="post" id="wpus-cart-button-form" class="wpus-cart-button-form '.__UP_strtolower_utf8($pieces['0']).'" action="" onsubmit="return ReadForm(this, true);">';  
			
			/* quantity */
			if(get_option('display_quantity') && get_option('display_quantity') == 1) { 
				$replacement .= '<label class="lp-label quantity">'.get_option('qualtity_text').' :</label><input type="text" name="quantity" value="1" size="4" />'.$option_break;
			} else {
				$replacement .= '<input type="hidden" name="quantity" value="1" />';
			}
			
			if(!empty($var_output)) { $replacement .= $var_output; }

			$replacement .= '<input type="hidden" name="product" value="'.$pieces['0'].'" />';
			
			/*
			/ price variation combo
			/ test if the price is unique or have variation
			*/
			
			if( preg_match('/\[(?P<label>\w+)/', $pieces['1']) ) {
				
				$priceVariation = str_replace('[','', $pieces['1']);
				$priceVariation = str_replace(']','', $priceVariation);
				$priceVariationArray = explode('|', $priceVariation);
				$variation_name = $priceVariationArray [0];
				
				$replacement .= '<label class="lp-label '.__UP_strtolower_utf8($variation_name).'">'.$variation_name.' :</label><select class="sp-select price" name="price">';
				for ($i=1;$i<sizeof($priceVariationArray); $i++) {
					$priceDigitAndWordArray = explode(',' , $priceVariationArray[$i]);
					$replacement .= '<option value="'.$priceDigitAndWordArray[0].','.$priceDigitAndWordArray[1].'">'.$priceDigitAndWordArray[0].'</option>';
				}
				$replacement .= '</select>'.$option_break;
				
			} elseif($pieces['1'] != "" ) { 
				$replacement .= '<input type="hidden" name="price" value="'.$pieces['1'].'" />'; 
			} else { echo( _("Error: no price configured") ); }				
			
			/* 
			/ shipping variation combo
			*/
			
			if(strpos($match,":shipping") > 0) {
				if( preg_match('/\[(?P<label>\w+)/', $pieces['2']) ) {
					
					$shippingVariation = str_replace('[','', $pieces['2']);
					$shippingVariation = str_replace(']','', $shippingVariation);
					$shippingVariationArray = explode('|', $shippingVariation);
					$variation_name = $shippingVariationArray [0];
					
					$replacement .= '<label class="vs-label '.__UP_strtolower_utf8($variation_name).'">'.$variation_name.' :</label><select class="sv-select shipping" name="shipping">';
					for ($i=1;$i<sizeof($shippingVariationArray); $i++) {
						$shippingDigitAndWordArray = explode(',' , $shippingVariationArray[$i]);
						$replacement .= '<option value="'
						.$shippingDigitAndWordArray[0].','
						.$shippingDigitAndWordArray[1].'">'
						.$shippingDigitAndWordArray[0].'</option>';
					}
					
					$replacement .= '</select>'.$option_break;
					
				} elseif($pieces['2'] != "" ) { 
					$replacement .= '<input type="hidden" name="shipping" value="'.$pieces['2'].'" />';
				}
			}
			
			/*
			/ all missing hidden fields
			*/
			
			$replacement .= '<input type="hidden" name="product_tmp" value="'.$pieces['0'].'" />';
			$replacement .= '<input type="hidden" name="cartLink" value="'.get_permalink($post->ID).'" />';
			$replacement .= '<input type="hidden" name="addcart" value="1" />';

			if(preg_match("/http/", $addcart)) {	// Use the image as the 'add to cart' button
				$replacement .= '<input class="image" type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WUSPSC")).'"/>';
			} else {
				$replacement .= '<input class="vsubmit submit" type="submit" value="'.$addcart.'" />';
			} 
			
			$replacement .= '</form>';
			$content = str_replace ($match, $replacement, $content);

		}

		return $content;
}


/* ------------------------------- to do ------------------------------------- */
/* Need to clean the following function                                        */
/* and create compatibility with print_wp_cart_action                          */
/* ------------------------------- to do ------------------------------------- */

function print_wp_cart_button_for_product($name, $price, $shipping=0) {
	$addcart = get_option('addToCartButtonName');
	
	if(!$addcart || ($addcart == '') )
		$addcart = __("Add to Cart", "WUSPSC");			   
	
	$replacement = '<object><form method="post" id="wpus-cart-button-form" class="wpus-cart-button-form '.__UP_strtolower_utf8($name).'" action="" onsubmit="return ReadForm(this, true);">';			 
	if(!empty($var_output)) {
		$replacement .= $var_output;
	}

	$replacement .= '<input type="hidden" name="product" value="'.$name.'" />';
	
// price variation combo
	if( preg_match('/\[(?P<label>\w+)/', $price) ) {
		
		$priceVariation = str_replace('[','', $price);
		$priceVariation = str_replace(']','', $priceVariation);
		$priceVariationArray = explode('|', $priceVariation);
		$variation_name = $priceVariationArray [0];
		
		$replacement .= '<label class="vp-label '.__UP_strtolower_utf8($variation_name).'">'.$variation_name.' :</label><select class="sp-select price" name="price">';
		for ($i=1;$i<sizeof($priceVariationArray); $i++) {
			$priceDigitAndWordArray = explode(',' , $priceVariationArray[$i]);
			$replacement .= '<option value="'
			.$priceDigitAndWordArray[0].','
			.$priceDigitAndWordArray[1].'">'
			.$priceDigitAndWordArray[0]
			.'</option>';
		}
		$replacement .= '</select>';
		
	} elseif($price != "" ) { 
		$replacement .= '<input type="hidden" name="price" value="'.$price.'" />'; 
	} else {
		echo( _("Error: no price configured") );
	}				
	
	if($shipping != '' ) {
		/* 
		/ shipping variation combo
		*/
		if( preg_match('/\[(?P<label>\w+)/', $shipping) ) {
			
			$shippingVariation = str_replace('[','', $shipping);
			$shippingVariation = str_replace(']','', $shippingVariation);
			$shippingVariationArray = explode('|', $shippingVariation);
			$variation_name = $shippingVariationArray [0];
			
			$replacement .= '<label class="vs-label '.__UP_strtolower_utf8($variation_name).'">'.$variation_name.' :</label><select class="sv-select shipping" name="shipping">';
			for ($i=1;$i<sizeof($shippingVariationArray); $i++) {
				$shippingDigitAndWordArray = explode(',' , $shippingVariationArray[$i]);
				$replacement .= '<option value="'
				.$shippingDigitAndWordArray[0].','
				.$shippingDigitAndWordArray[1].'">'
				.$shippingDigitAndWordArray[0].'</option>';
			}
			$replacement .= '</select>';
		} elseif( $shipping > 0 ) { 
			$replacement .= '<input type="hidden" name="shipping" value="'.$shipping.'" />';
		}					
	}
	
	$replacement .= '<input type="hidden" name="product_tmp" value="'.$name.'" />';
	$replacement .= '<input type="hidden" name="cartLink" value="'.get_permalink($post->ID).'" />';
	$replacement .= '<input type="hidden" name="addcart" value="1" />';

	if(preg_match("/http/", $addcart)) { // Use the image as the 'add to cart' button
		$replacement .= '<input class="image" type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WUSPSC")).'"/>';
	} else {
		$replacement .= '<input class="vsubmit submit" type="submit" value="'.$addcart.'" />';
	} 
		
	$replacement .= '</form></object>';   
	
	return $replacement;
}

/* ------------------------------- to do ------------------------------------- */
/* Future                                                                      */
/* ------------------------------- to do ------------------------------------- */
/*
function print_wp_cart_button_for_product($name, $price, $shipping=0, $variation='' ) {

	// test if the price have variations
	if ($price != '') {
		
		if ( is_int($price) || is_float($price)) {
			$pricevalue = round($price,2);
		} elseif (is_array($price)) {
		
		}
	}
	
	// test if the shipping have variations
	if ($shipping != '') {
		
		if ( is_int($shipping) || is_float($shipping)) {
			$shippingvalue = round($shipping,2);
		} elseif (is_array($shipping)) {
		
		}
	}
	
	// test if there is variations of the same product
	if ($variation != '') {
		
		if ( is_string($variation) ) {
		
		} elseif (is_array($variation)) {
		
		}
	}
	
	$content = "[wp_cart:".$name.":price:".$pricevalue.":shipping:".$shippingvalue.$variationvalue":end]";
	print_wp_cart_action($content);
}
*/
/* ------------------------------- end to do -------------------------------- */

/*  Hooks filter action : http://codex.wordpress.org/Function_Reference/add_filter */
//add_filter('the_content', 'print_wp_cart_button',11);
add_filter('the_content', 'print_wp_cart_action',11);
add_filter('the_content', 'shopping_cart_show');

/* Shortcode : http://codex.wordpress.org/Function_Reference/add_shortcode */
add_shortcode('show_wp_shopping_cart', 'show_wpus_shopping_cart_handler');
add_shortcode('show_wpus_shopping_cart', 'show_wpus_shopping_cart_handler');
add_shortcode('validate_wp_shopping_cart', 'validate_wpus_shopping_cart_handler');
add_shortcode('validate_wpus_shopping_cart', 'validate_wpus_shopping_cart_handler');
add_shortcode('always_show_wpus_shopping_cart', 'always_show_cart_handler');

add_action('wp_head', 'wp_cart_add_read_form_javascript');


?>