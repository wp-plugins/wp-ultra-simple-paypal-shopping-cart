<?php
/*
Plugin Name: WP Ultra simple Paypal Cart
Version: v4.0.1
Plugin URI: http://www.ultra-prod.com/?p=86
Author: Mike Castro Demaria
Author URI: http://www.ultra-prod.com
Description: WP Ultra simple Paypal Cart Plugin,  use PayPal API to easely add Shopping Cart in your post or your page (you need to <a href="https://www.paypal.com/fr/mrb/pal=DKBDRZGU62JYC">create a PayPal account</a>).
Added differents features like PayPal sandbox test, Price Variations, interface text's personalization, CSS call for button and many other improvements and bugs corrections too.
This plug-in is based on the based on Ruhul Amin's "Simple Paypal Shopping Cart" v3.2.3 http://www.tipsandtricks-hq.com/?p=768) .
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
if(!isset($_SESSION)) 
{
	session_start();
}	

define('WP_CART_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_CART_URL', plugins_url('',__FILE__));

// loading language files
load_plugin_textdomain('WUSPSC', false, WP_CART_FOLDER . '/languages');

add_option('wp_cart_title', __("Your Shopping Cart", "WUSPSC"));
add_option('wp_cart_empty_text', __("Your cart is empty", "WUSPSC"));

add_option('wp_cart_visit_shop_text', __('Visit The Shop', "WUSPSC"));
add_option('wp_cart_update_quantiy_text', __('Hit enter to submit new Quantity.', "WUSPSC"));
add_option('subtotal_text', __('Subtotal', "WUSPSC"));
add_option('shipping_text', __('Shipping', "WUSPSC"));
add_option('total_text', __('Total', "WUSPSC"));
add_option('item_name_text', __('Item Name', "WUSPSC"));
add_option('qualtity_text', __('Quantity', "WUSPSC"));
add_option('price_text', __('Price', "WUSPSC"));
add_option('remove_text', __("Remove", "WUSPSC"));
add_option('add_cartstyle', '');
add_option('cart_currency_symbol_order', '1');

add_option('cart_return_from_paypal_url', get_bloginfo('wpurl'));

function always_show_cart_handler($atts) 
{
	return print_wp_shopping_cart();
}

function show_wp_shopping_cart_handler()
{
    if (cart_not_empty())
    {
       	$output = print_wp_shopping_cart();
    }
    return $output;	
}

function shopping_cart_show($content)
{
	if (strpos($content, "<!--show-wp-shopping-cart-->") !== FALSE)
    {
    	if (cart_not_empty())
    	{
        	$content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        	$matchingText = '<!--show-wp-shopping-cart-->';
        	$replacementText = print_wp_shopping_cart();
        	$content = str_replace($matchingText, $replacementText, $content);
    	}
    }
    return $content;
}

// Reset the Cart as this is a returned customer from Paypal
if (isset($_GET["merchant_return_link"]) && !empty($_GET["merchant_return_link"]))
{
    reset_wp_cart();
    header('Location: ' . get_option('cart_return_from_paypal_url'));
}

if (isset($_GET["mc_gross"])&&  $_GET["mc_gross"]> 0)
{
    reset_wp_cart();
    header('Location: ' . get_option('cart_return_from_paypal_url'));
}

//Clear the cart if the customer landed on the thank you page
if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page'))
{
	if(get_option('cart_return_from_paypal_url') == cart_current_page_url())
	{
		reset_wp_cart();
	}
}

function reset_wp_cart()
{
    $products = $_SESSION['ultraSimpleCart'];
    if(empty($products))
    {
    	unset($_SESSION['ultraSimpleCart']);
    	return;
    }
    foreach ($products as $key => $item)
    {
        unset($products[$key]);
    }
    $_SESSION['ultraSimpleCart'] = $products;    
}

if ($_POST['addcart'])
{
	$domain_url = $_SERVER['SERVER_NAME'];
	$cookie_domain = str_replace("www","",$domain_url);    	
	setcookie("cart_in_use","true",time()+21600,"/",$cookie_domain);  //useful to not serve cached page when using with a caching plugin
    $count = 1;    
    $products = $_SESSION['ultraSimpleCart'];
    
    if (is_array($products))
    {
        foreach ($products as $key => $item)
        {
            if ($item['name'] == stripslashes($_POST['product']))
            {
                $count += $item['quantity'];
                $item['quantity']++;
                unset($products[$key]);
                array_push($products, $item);
            }
        }
    }
    else
    {
        $products = array();
    }
        
    if ($count == 1)
    {
        if (!empty($_POST[$_POST['product']]))
            $price = $_POST[$_POST['product']];
        else
            $price = $_POST['price'];
        
        $product = array('name' => stripslashes($_POST['product']), 'price' => $price, 'quantity' => $count, 'shipping' => $_POST['shipping'], 'cartLink' => $_POST['cartLink'], 'item_number' => $_POST['item_number']);
        array_push($products, $product);
    }
    
    sort($products);
    $_SESSION['ultraSimpleCart'] = $products;
    
    if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page'))
    {
    	$checkout_url = get_option('cart_checkout_page_url');
    	if(empty($checkout_url))
    	{
    		echo "<br /><strong>".(__("Shopping Cart Configuration Error! You must specify a value in the 'Checkout Page URL' field for the automatic redirection feature to work!", "WUSPSC"))."</strong><br />";
    	}
    	else
    	{
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
    	}
    }    
}
else if ($_POST['cquantity'])
{
    $products = $_SESSION['ultraSimpleCart'];
    foreach ($products as $key => $item)
    {
        if ((stripslashes($item['name']) == stripslashes($_POST['product'])) && $_POST['quantity'])
        {
            $item['quantity'] = $_POST['quantity'];
            unset($products[$key]);
            array_push($products, $item);
        }
        else if (($item['name'] == stripslashes($_POST['product'])) && !$_POST['quantity'])
            unset($products[$key]);
    }
    sort($products);
    $_SESSION['ultraSimpleCart'] = $products;
}
else if ($_POST['delcart'])
{
    $products = $_SESSION['ultraSimpleCart'];
    foreach ($products as $key => $item)
    {
        if ($item['name'] == stripslashes($_POST['product']))
            unset($products[$key]);
    }
    $_SESSION['ultraSimpleCart'] = $products;
}

function get_the_price( $pricestr ){
        $pos = stripos($pricestr, ",");
        if ( $pos !== false ) {
                $pricearray = explode(",", $pricestr );
                $price = $pricearray[1];
        } else { 
                $price = $pricestr; 
        }
        return $price;
}

function get_the_name( $namestr ){

	if(preg_match("/\(([^\)]*)\).*/", $namestr, $matched)) {     
    	$namearray = explode(",", $matched[1] );
    	$name = str_ireplace ( $matched[1] , $namearray[0], $namestr );
	} else {
		$name = $namestr ;
	}
	
	return $name;
} 

function print_wp_shopping_cart()
{
	if (!cart_not_empty())
	{
	    $empty_cart_text = get_option('wp_cart_empty_text');
	    $wp_cart_visit_shop_text = get_option('wp_cart_visit_shop_text');
		if (!empty($empty_cart_text)) 
		{
			if (preg_match("/http/", $empty_cart_text))
			{
				$output .= '<img src="'.$empty_cart_text.'" alt="'.$empty_cart_text.'" />';
			}
			else
			{
				$output .= $empty_cart_text;
			}			
		}
		$cart_products_page_url = get_option('cart_products_page_url');
		if (!empty($cart_products_page_url))
		{
			$output .= '<br /><a rel="nofollow" href="'.$cart_products_page_url.'">'.$wp_cart_visit_shop_text.'</a>';
		}		
		return $output;
	}
    $email = get_bloginfo('admin_email');
    $use_affiliate_platform = get_option('wp_use_aff_platform');   
    $defaultCurrency = get_option('cart_payment_currency');
    $defaultSymbol = get_option('cart_currency_symbol');
    $defaultEmail = get_option('cart_paypal_email');
    if (!empty($defaultCurrency))
        $paypal_currency = $defaultCurrency;
    else
        $paypal_currency = __("USD", "WUSPSC");
    if (!empty($defaultSymbol))
        $paypal_symbol = $defaultSymbol;
    else
        $paypal_symbol = __("$", "WUSPSC");

    if (!empty($defaultEmail))
        $email = $defaultEmail;
     
    $decimal = '.';  
	$urls = '';
        
    $return = get_option('cart_return_from_paypal_url');
            
    if (!empty($return))
        $urls .= '<input type="hidden" name="return" value="'.$return.'" />';
	
	if ($use_affiliate_platform)  
	{
		if (function_exists('wp_aff_platform_install'))
		{
			$notify = WP_AFF_PLATFORM_URL.'/api/ipn_handler.php';
			//$notify = WP_CART_URL.'/paypal.php';
			$urls .= '<input type="hidden" name="notify_url" value="'.$notify.'" />';
		}
	}
	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = __("Your Shopping Cart", "WUSPSC");
    
    global $plugin_dir_name;
    $output .= '<div class="shopping_cart" style=" padding: 5px;">';
    if (!get_option('wp_shopping_cart_image_hide'))    
    {    	
    	$output .= "<img src='".WP_CART_URL."/images/shopping_cart_icon.png' value='".(__("Cart", "WUSPSC"))."' title='".(__("Cart", "WUSPSC"))."' />";
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
    
    $count = 1;
    $total_items = 0;
    $total = 0;
    $form = '';
    if ($_SESSION['ultraSimpleCart'] && is_array($_SESSION['ultraSimpleCart']))
    {   
        $output .= '
        <tr>
        <th style="text-align: left">'.get_option('item_name_text').'</th><th>'.get_option('qualtity_text').'</th><th>'.get_option('price_text').'</th>
        </tr>';
    
	    foreach ($_SESSION['ultraSimpleCart'] as $item)
	    {
	        $total += get_the_price($item['price']) * $item['quantity'];
	        $item_total_shipping += $item['shipping'] * $item['quantity'];
	        $total_items +=  $item['quantity'];
	    }
		
	    if( $item_total_shipping == 0)
	    {
	    	$baseShipping = get_option('cart_base_shipping_cost');
	    	$postage_cost = $item_total_shipping + $baseShipping;
	    }
	    else
	    {
	    	//$postage_cost = 0;
			$postage_cost = $item_total_shipping;
	    }
	    
	    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');
	    if (!empty($cart_free_shipping_threshold) && $total > $cart_free_shipping_threshold)
	    {
	    	$postage_cost = 0;
	    }
	    
	    foreach ($_SESSION['ultraSimpleCart'] as $item)
	    {
	    
	    	$price = get_the_price( $item['price'] );
	    	$name = get_the_name( $item['name'] );
	    	
	        $output .= "
	        <tr><td style='overflow: hidden;'><a href='".$item['cartLink']."'>".$name."</a></td>
	        <td style='text-align: center'><form method=\"post\"  action=\"\" name='pcquantity' style='display: inline'>
                <input type=\"hidden\" name=\"product\" value=\"".$name."\" />

	        <input type='hidden' name='cquantity' value='1' /><input type='text' name='quantity' value='".$item['quantity']."' size='1' onchange='document.pcquantity.submit();' onkeypress='document.getElementById(\"pinfo\").style.display = \"\";' /></form></td>
	        <td style='text-align: center'>".print_payment_currency(($price * $item['quantity']), $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td>
	        <td><form method=\"post\"  action=\"\">
	        <input type=\"hidden\" name=\"product\" value=\"".$item['name']."\" />
	        <input type='hidden' name='delcart' value='1' />
	        <input type='image' src='".WP_CART_URL."/images/Shoppingcart_delete.png' value='".get_option('remove_text')."' title='".get_option('remove_text')."' /></form></td></tr>
	        ";
	        
	        $form .= "
	            <input type=\"hidden\" name=\"item_name_$count\" value=\"".$name."\" />
	            <input type=\"hidden\" name=\"amount_$count\" value='".$price."' />
	            <input type=\"hidden\" name=\"quantity_$count\" value=\"".$item['quantity']."\" />
	            <input type='hidden' name='item_number' value='".$item['item_number']."' />
	        ";        
	        $count++;
	    }
	    if (!get_option('wp_shopping_cart_use_profile_shipping'))
	    {
	    	$postage_cost = number_format($postage_cost,2);
	    	$form .= "<input type=\"hidden\" name=\"shipping_1\" value='".$postage_cost."' />";  
	    }
	    if (get_option('wp_shopping_cart_collect_address'))//force address collection
	    {
	    	$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"2\" />";  
	    }	    	    
    }
    
       	$count--;
       	
       	if ($count)
       	{
       		//$output .= '<tr><td></td><td></td><td></td></tr>';  

            if ($postage_cost != 0)
            {
                $output .= "
                <tr><td colspan='2' style='font-weight: bold; text-align: right;'>".get_option('subtotal_text').": </td><td style='text-align: center'>".print_payment_currency($total, $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td><td></td></tr>
                <tr><td colspan='2' style='font-weight: bold; text-align: right;'>".get_option('shipping_text').": </td><td style='text-align: center'>".print_payment_currency($postage_cost, $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td><td></td></tr>";
            }

            $output .= "
       		<tr><td colspan='2' style='font-weight: bold; text-align: right;'>".get_option('total_text').": </td><td style='text-align: center'>".print_payment_currency(($total+$postage_cost), $paypal_symbol, $decimal, get_option('cart_currency_symbol_order'))."</td><td></td></tr>
       		<tr><td colspan='4'>";
       		
       			if (get_option('is_sandbox') == "1") { $is_sandbox = "sandbox."; } else { $is_sandbox = ""; }
       
              	$output .= "<form action=\"https://www.".$is_sandbox."paypal.com/cgi-bin/webscr\" method=\"post\">$form";
    			if ($count)
            		$output .= '<input type="image" src="'.WP_CART_URL.'/images/'.(__("paypal_checkout_EN.png", "WUSPSC")).'" name="submit" class="wp_cart_checkout_button" alt="'.(__("Make payments with PayPal - it\'s fast, free and secure!", "WUSPSC")).'" />';
       
    			$output .= $urls.'
			    <input type="hidden" name="business" value="'.$email.'" />
			    <input type="hidden" name="currency_code" value="'.$paypal_currency.'" />
			    <input type="hidden" name="cmd" value="_cart" />
			    <input type="hidden" name="upload" value="1" />
			    <input type="hidden" name="rm" value="2" />
			    <input type="hidden" name="mrb" value="DKBDRZGU62JYC" />';
			    if ($use_affiliate_platform)
			    {
			    	$output .= wp_cart_add_custom_field();
			    }
			    $output .= '</form>';          
       	}       
       	$output .= "       
       	</td></tr>
    	</table></div>
    	";
    
    return $output;
}
// https://www.sandbox.paypal.com/cgi-bin/webscr (paypal testing site)
// https://www.paypal.com/us/cgi-bin/webscr (paypal live site )

function wp_cart_add_custom_field()
{
	if (function_exists('wp_aff_platform_install'))
	{
		$output = '';
		if (!empty($_SESSION['ap_id']))
		{
			$output = '<input type="hidden" name="custom" value="'.$_SESSION['ap_id'].'" id="wp_affiliate" />';
		}
		else if (isset($_COOKIE['ap_id']))
		{
			$output = '<input type="hidden" name="custom" value="'.$_COOKIE['ap_id'].'" id="wp_affiliate" />';
		}
		return 	$output;
	}
}

function print_wp_cart_action($content)
{
	//wp_cart_add_read_form_javascript();
        
        $addcart = get_option('addToCartButtonName');    
        if (!$addcart || ($addcart == '') )
            $addcart = __("Add to Cart", "WUSPSC");
            	
        $pattern = '#\[wp_cart:.+:price:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {   
        	$var_output = '';
            $pos = strpos($match,":var1");
			if ($pos)
			{				
				$match_tmp = $match;
				// Variation control is used
				$pos2 = strpos($match,":var2");
				if ($pos2)
				{
					//echo '<br />'.$match_tmp.'<br />';
					$pattern = '#var2\[.*]:#';
				    preg_match_all ($pattern, $match_tmp, $matches3);
				    $match3 = $matches3[0][0];
				    //echo '<br />'.$match3.'<br />';
				    $match_tmp = str_replace ($match3, '', $match_tmp);
				    
				    $pattern = 'var2[';
				    $m3 = str_replace ($pattern, '', $match3);
				    $pattern = ']:';
				    $m3 = str_replace ($pattern, '', $m3);  
				    $pieces3 = explode('|',$m3);
			
				    $variation2_name = $pieces3[0];
				    $var_output .= $variation2_name." : ";
				    $var_output .= '<select name="variation2" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces3); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces3[$i].'">'.$pieces3[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				    
				}				
			    
			    $pattern = '#var1\[.*]:#';
			    preg_match_all ($pattern, $match_tmp, $matches2);
			    $match2 = $matches2[0][0];

			    $match_tmp = str_replace ($match2, '', $match_tmp);

				    $pattern = 'var1[';
				    $m2 = str_replace ($pattern, '', $match2);
				    $pattern = ']:';
				    $m2 = str_replace ($pattern, '', $m2);  
				    $pieces2 = explode('|',$m2);
			
				    $variation_name = $pieces2[0];
				    $var_output .= $variation_name." : ";
				    $var_output .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces2); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces2[$i].'">'.$pieces2[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				

			}

            $pattern = '[wp_cart:';
            $m = str_replace ($pattern, '', $match);
            
            $pattern = 'price:';
            $m = str_replace ($pattern, '', $m);
            $pattern = 'shipping:';
            $m = str_replace ($pattern, '', $m);
            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode(':',$m);
            /*
            echo("<pre>");
            var_dump ( $pieces );
    		echo("</pre>");
    		*/
    		/*
    		echo("<pre>");
            var_dump ( $_SESSION['ultraSimpleCart'] );
    		echo("</pre>");
    		*/
                $replacement = '<object>';
                $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);">';             
                if (!empty($var_output))
                {
                	$replacement .= $var_output;
                }

                $replacement .= '<input type="hidden" name="product" value="'.$pieces['0'].'" />';
				
				// test if the price is unique or have variation
				/*
				echo("<pre>");
				preg_match('/\[(?P<label>\w+)/', $pieces['1'], $matches);
				print_r($matches);
				print_r($pieces['1']);
    			echo("</pre>");
				*/
				// price variation combo
                if ( preg_match('/\[(?P<label>\w+)/', $pieces['1']) ) {
                	
                	$priceVariation = str_replace('[','', $pieces['1']);
                	$priceVariation = str_replace(']','', $priceVariation);
                	$priceVariationArray = explode('|', $priceVariation);
                	$variation_name = $priceVariationArray [0];
                	
                	$replacement .= $variation_name." : ".'<select class="price" name="price">';
                	for ($i=1;$i<sizeof($priceVariationArray); $i++)
				    {
				    	$priceDigitAndWordArray = explode(',' , $priceVariationArray[$i]);
				    	$replacement .= '<option value="'.$priceDigitAndWordArray[0].','.$priceDigitAndWordArray[1].'">'.$priceDigitAndWordArray[0].'</option>';
				    }
                	$replacement .= '</select>';
                	
                } 
                elseif ($pieces['1'] != "" ) { 
                	$replacement .= '<input type="hidden" name="price" value="'.$pieces['1'].'" />'; 
                }
                else { echo( _("Error: no price configured") ); }                
                
                $replacement .= '<input type="hidden" name="product_tmp" value="'.$pieces['0'].'" />';
                if (sizeof($pieces) >2 )
                {
                	//we have shipping
                	$replacement .= '<input type="hidden" name="shipping" value="'.$pieces['2'].'" />';
                }
                $replacement .= '<input type="hidden" name="cartLink" value="'.cart_current_page_url().'" />';
                $replacement .= '<input type="hidden" name="addcart" value="1" />';

				if (preg_match("/http/", $addcart)) // Use the image as the 'add to cart' button
				{
				    $replacement .= '<input class="image" type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WUSPSC")).'"/>';
				} 
				else 
				{
				    $replacement .= '<input class="submit" type="submit" value="'.$addcart.'" />';
				} 
				
				$replacement .= '</form>';
				
                $replacement .= '</object>';
                $content = str_replace ($match, $replacement, $content);                
        }
        return $content;	
}

function print_wp_cart_button_new($content)
{
	//wp_cart_add_read_form_javascript();
        
        $addcart = get_option('addToCartButtonName');    
        if (!$addcart || ($addcart == '') )
            $addcart = __("Add to Cart", "WUSPSC");
            	
        $pattern = '#\[wp_cart:.+:price:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {   
        	$var_output = '';
            $pos = strpos($match,":var1");
			if ($pos)
			{				
				$match_tmp = $match;
				// Variation control is used
				$pos2 = strpos($match,":var2");
				if ($pos2)
				{
					//echo '<br />'.$match_tmp.'<br />';
					$pattern = '#var2\[.*]:#';
				    preg_match_all ($pattern, $match_tmp, $matches3);
				    $match3 = $matches3[0][0];
				    //echo '<br />'.$match3.'<br />';
				    $match_tmp = str_replace ($match3, '', $match_tmp);
				    
				    $pattern = 'var2[';
				    $m3 = str_replace ($pattern, '', $match3);
				    $pattern = ']:';
				    $m3 = str_replace ($pattern, '', $m3);  
				    $pieces3 = explode('|',$m3);
			
				    $variation2_name = $pieces3[0];
				    $var_output .= $variation2_name." : ";
				    $var_output .= '<select name="variation2" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces3); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces3[$i].'">'.$pieces3[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				    
				}				
			    
			    $pattern = '#var1\[.*]:#';
			    preg_match_all ($pattern, $match_tmp, $matches2);
			    $match2 = $matches2[0][0];

			    $match_tmp = str_replace ($match2, '', $match_tmp);

				    $pattern = 'var1[';
				    $m2 = str_replace ($pattern, '', $match2);
				    $pattern = ']:';
				    $m2 = str_replace ($pattern, '', $m2);  
				    $pieces2 = explode('|',$m2);
			
				    $variation_name = $pieces2[0];
				    $var_output .= $variation_name." : ";
				    $var_output .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces2); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces2[$i].'">'.$pieces2[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				

			}

            $pattern = '[wp_cart:';
            $m = str_replace ($pattern, '', $match);
            
            $pattern = 'price:';
            $m = str_replace ($pattern, '', $m);
            $pattern = 'shipping:';
            $m = str_replace ($pattern, '', $m);
            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode(':',$m);
    
                $replacement = '<object>';
                $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);">';             
                if (!empty($var_output))
                {
                	$replacement .= $var_output;
                } 
				                
				if (preg_match("/http/", $addcart)) // Use the image as the 'add to cart' button
				{
				    $replacement .= '<input type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WUSPSC")).'"/>';
				} 
				else 
				{
				    $replacement .= '<input type="submit" value="'.$addcart.'" />';
				} 

                $replacement .= '<input type="hidden" name="product" value="'.$pieces['0'].'" />';
                $replacement .= '<input type="hidden" name="price" value="'.$pieces['1'].'" />';
                $replacement .= '<input type="hidden" name="product_tmp" value="'.$pieces['0'].'" />';
                if (sizeof($pieces) >2 )
                {
                	//we have shipping
                	$replacement .= '<input type="hidden" name="shipping" value="'.$pieces['2'].'" />';
                }
                $replacement .= '<input type="hidden" name="cartLink" value="'.cart_current_page_url().'" />';
                $replacement .= '<input type="hidden" name="addcart" value="1" /></form>';
                $replacement .= '</object>';
                $content = str_replace ($match, $replacement, $content);                
        }
        return $content;	
}

function wp_cart_add_read_form_javascript()
{
	echo '
	<script type="text/javascript">
	<!--
	//
	function ReadForm (obj1, tst) 
	{ 
	    // Read the user form
	    var i,j,pos;
	    val_total="";
	    val_combo="";		
	
	    for (i=0; i<obj1.length; i++) 
	    {     
	        // run entire form
	        obj = obj1.elements[i];           // a form element
	
	        if (obj.type == "select-one") 
	        {   // just selects
	            if (obj.name == "quantity" ||
	                obj.name == "amount") continue;
		        pos = obj.selectedIndex;        // which option selected
		        val = obj.options[pos].value;   // selected value
		        val_combo = val_combo + "(" + val + ")";
	        }
	    }
		// Now summarize everything we have processed above
		val_total = obj1.product_tmp.value + val_combo;
		obj1.product.value = val_total;
	}
	//-->
	</script>';	
}
function print_wp_cart_button_for_product($name, $price, $shipping=0)
{
        $addcart = get_option('addToCartButtonName');
    
        if (!$addcart || ($addcart == '') )
            $addcart = __("Add to Cart", "WUSPSC");
                  

        $replacement = '<object><form method="post" class="wp-cart-button-form" action="" style="display:inline">';
		if (preg_match("/http:/", $addcart)) // Use the image as the 'add to cart' button
		{
			$replacement .= '<input type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WUSPSC")).'"/>';
		} 
		else 
		{
		    $replacement .= '<input type="submit" value="'.$addcart.'" />';
		}             	      

        $replacement .= '<input type="hidden" name="product" value="'.$name.'" /><input type="hidden" name="price" value="'.$price.'" /><input type="hidden" name="shipping" value="'.$shipping.'" /><input type="hidden" name="addcart" value="1" /><input type="hidden" name="cartLink" value="'.cart_current_page_url().'" /></form></object>';
                
        return $replacement;
}

function cart_not_empty()
{
        $count = 0;
        if (isset($_SESSION['ultraSimpleCart']) && is_array($_SESSION['ultraSimpleCart']))
        {
            foreach ($_SESSION['ultraSimpleCart'] as $item)
                $count++;
            return $count;
        }
        else
            return 0;
}

function print_payment_currency($price, $symbol, $decimal, $defaultSymbolOrder)
{
	switch ( $defaultSymbolOrder )
	{
	    
	    case "1":
	        $priceSymbol = $symbol.number_format($price, 2, $decimal, ',');
	        break;
	    case "2":
	        $priceSymbol = number_format($price, 2, $decimal, ',').$symbol;
	        break;
	    default:
	        $priceSymbol = $symbol.number_format($price, 2, $decimal, ',');
	        break;
	}
	
    return $priceSymbol;
    //return $symbol.number_format($price, 2, $decimal, ',');
}

function cart_current_page_url() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function show_wp_cart_options_page () {	
	$wp_ultra_simple_paypal_shopping_cart_version = "4.0.0";
	
    if (isset($_POST['info_update']))
    {
        update_option('cart_payment_currency', (string)$_POST["cart_payment_currency"]);
        update_option('cart_currency_symbol', (string)$_POST["cart_currency_symbol"]);
        update_option('cart_currency_symbol_order', (string)$_POST["cart_currency_symbol_order"]);
        update_option('cart_base_shipping_cost', (string)$_POST["cart_base_shipping_cost"]);
        update_option('cart_free_shipping_threshold', (string)$_POST["cart_free_shipping_threshold"]);   
        update_option('wp_shopping_cart_collect_address', ($_POST['wp_shopping_cart_collect_address']!='') ? 'checked="checked"':'' );    
        update_option('wp_shopping_cart_use_profile_shipping', ($_POST['wp_shopping_cart_use_profile_shipping']!='') ? 'checked="checked"':'' );
                
        update_option('cart_paypal_email', (string)$_POST["cart_paypal_email"]);
        update_option('addToCartButtonName', (string)$_POST["addToCartButtonName"]);
        update_option('wp_cart_title', (string)$_POST["wp_cart_title"]);
        update_option('wp_cart_empty_text', (string)$_POST["wp_cart_empty_text"]);
        update_option('cart_return_from_paypal_url', (string)$_POST["cart_return_from_paypal_url"]);
        update_option('cart_products_page_url', (string)$_POST["cart_products_page_url"]);
        
        // txt string
        update_option('wp_cart_visit_shop_text', (string)$_POST["wp_cart_visit_shop_text"]);
        update_option('wp_cart_update_quantiy_text', (string)$_POST["wp_cart_update_quantiy_text"]);
        update_option('subtotal_text', (string)$_POST["subtotal_text"]);
        update_option('shipping_text', (string)$_POST["shipping_text"]);
        update_option('total_text', (string)$_POST["total_text"]);
        update_option('item_name_text', (string)$_POST["item_name_text"]);
        update_option('qualtity_text', (string)$_POST["qualtity_text"]);
        update_option('price_text', (string)$_POST["price_text"]);
        update_option('item_name_text', (string)$_POST["item_name_text"]);
        update_option('qualtity_text', (string)$_POST["qualtity_text"]);
        update_option('price_text', (string)$_POST["price_text"]);
        update_option('remove_text', (string)$_POST["remove_text"]);
        update_option('add_cartstyle', (string)$_POST["add_cartstyle"]);
        // sandbox option
        update_option('is_sandbox', (string)$_POST["is_sandbox"]);
                
        update_option('wp_shopping_cart_auto_redirect_to_checkout_page', ($_POST['wp_shopping_cart_auto_redirect_to_checkout_page']!='') ? 'checked="checked"':'' );
        update_option('cart_checkout_page_url', (string)$_POST["cart_checkout_page_url"]);
        update_option('wp_shopping_cart_reset_after_redirection_to_return_page', ($_POST['wp_shopping_cart_reset_after_redirection_to_return_page']!='') ? 'checked="checked"':'' );        
                
        update_option('wp_shopping_cart_image_hide', ($_POST['wp_shopping_cart_image_hide']!='') ? 'checked="checked"':'' );
        update_option('wp_use_aff_platform', ($_POST['wp_use_aff_platform']!='') ? 'checked="checked"':'' );
        
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.(__("Options Updated!", "WUSPSC")).'</strong></p></div>';
    }	
	
    $defaultCurrency = get_option('cart_payment_currency');    
    if (empty($defaultCurrency)) $defaultCurrency = __("USD", "WUSPSC");
    
    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = __("$", "WUSPSC");
    
    // Symbol order
	$defaultSymbolOrder = get_option('cart_currency_symbol_order');
	if (empty($defaultSymbolOrder)) { $defaultSymbolOrder = "1"; }
	// 
	if ( $defaultSymbolOrder == "1"){ $defaultSymbolOrderChecked1 = "checked"; $defaultSymbolOrderChecked2 = "";}
	elseif ( $defaultSymbolOrder == "2"){ $defaultSymbolOrderChecked1 = ""; $defaultSymbolOrderChecked2 = "checked"; }
	else { $defaultSymbolOrderChecked1 = ""; $defaultSymbolOrderChecked2 = ""; }

    $baseShipping = get_option('cart_base_shipping_cost');
    if (empty($baseShipping)) $baseShipping = 0;
    
    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');

    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
    $return_url =  get_option('cart_return_from_paypal_url');

    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = __("Add to Cart", "WUSPSC");           

	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = __("Your Shopping Cart", "WUSPSC");
	
	// sandbox
	$defaultSandboxChecked = get_option('is_sandbox');
	if ($defaultSandboxChecked == "1"){$defaultSandboxChecked1 = "checked"; $defaultSandboxChecked2 = "";}
	else {$defaultSandboxChecked1 = ""; $defaultSandboxChecked2 = "checked";}
	
	$emptyCartText = get_option('wp_cart_empty_text');
	$cart_products_page_url = get_option('cart_products_page_url');	 

	$cart_checkout_page_url = get_option('cart_checkout_page_url');
    if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page'))
        $wp_shopping_cart_auto_redirect_to_checkout_page = 'checked="checked"';
    else
        $wp_shopping_cart_auto_redirect_to_checkout_page = '';	
    
    // added txt string    
   	$wp_cart_visit_shop_text = get_option('wp_cart_visit_shop_text');
	$wp_cart_update_quantiy_text = get_option('wp_cart_update_quantiy_text');
	$subtotal_text = get_option('subtotal_text');
	$shipping_text = get_option('shipping_text');
	$total_text = get_option('total_text');
	$item_name_text = get_option('item_name_text');
	$qualtity_text = get_option('qualtity_text');
	$price_text = get_option('price_text');
	$remove_text = get_option('remove_text');
	$add_cartstyle = get_option('add_cartstyle');
        
    if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page'))
        $wp_shopping_cart_reset_after_redirection_to_return_page = 'checked="checked"';
    else
        $wp_shopping_cart_reset_after_redirection_to_return_page = '';	
                	    
    if (get_option('wp_shopping_cart_collect_address'))
        $wp_shopping_cart_collect_address = 'checked="checked"';
    else
        $wp_shopping_cart_collect_address = '';
        
    if (get_option('wp_shopping_cart_use_profile_shipping'))
        $wp_shopping_cart_use_profile_shipping = 'checked="checked"';
    else
        $wp_shopping_cart_use_profile_shipping = '';
                	
    if (get_option('wp_shopping_cart_image_hide'))
        $wp_cart_image_hide = 'checked="checked"';
    else
        $wp_cart_image_hide = '';

    if (get_option('wp_use_aff_platform'))
        $wp_use_aff_platform = 'checked="checked"';
    else
        $wp_use_aff_platform = '';
                              
	?>
 	
 	<h2><?php _e("WP Ultra Simple Shopping Cart Settings", "WUSPSC"); ?> v <?php echo $wp_ultra_simple_paypal_shopping_cart_version; ?></h2>
 	<p><?php _e("For information, updates and detailed documentation, please visit:", "WUSPSC"); ?> <a href="http://www.ultra-prod.com/?p=86">ultra-prod.com</a></p>
    <p><?php _e("For support, please use our dedicated forum:", "WUSPSC"); ?> <a href="http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3.0/"><?php _e("WPUSPSC Support Forum", "WUSPSC"); ?></a></p>

    <fieldset class="options">
		<p><h4><a href="https://www.paypal.com/fr/mrb/pal=DKBDRZGU62JYC"><?php _e("1. create a PayPal account (no cost for basic account)", "WUSPSC"); ?></a></h4>
			
        <p><h4><?php _e("2. Create post or page presenting the product or service and add caddy shortcode in the post. See example and possibilities following:", "WUSPSC"); ?></h4>
    <ul>
    	<ol>
    		<?php _e("To add the 'Add to Cart' button simply add the trigger text to a post or page, next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.", "WUSPSC"); ?><br />
    		<strong>[wp_cart:<?php _e("PRODUCT-NAME", "WUSPSC"); ?>:price:<?php _e("PRODUCT-PRICE", "WUSPSC"); ?>:end]</strong><br />
    		<blockquote><?php _e("eg.", "WUSPSC"); ?> [wp_cart:<?php _e("Test Product", "WUSPSC"); ?>:price:15.00:end]</blockquote>
    	</ol>

		<ol>
			<?php _e("To use variation of the price use the following trigger text:", "WUSPSC"); ?><br />
			<strong>[wp_cart:<?php _e("PRODUCT-NAME", "WUSPSC"); ?>:price:[<?php _e("VARIATION-NAME", "WUSPSC"); ?>|<?php _e("VARIATION-LABEL1", "WUSPSC"); ?>,<?php _e("VARIATION-PRICE1", "WUSPSC"); ?>|<?php _e("VARIATION-LABEL2", "WUSPSC"); ?>,<?php _e("VARIATION-PRICE2", "WUSPSC"); ?>]:end]</strong><br />
			<blockquote><?php _e("eg.", "WUSPSC"); ?> [wp_cart:<?php _e("Test Product", "WUSPSC"); ?>:price:[<?php _e("Size|Small,1.10|Medium,2.10|Large,3.10", "WUSPSC"); ?>]:end]</blockquote>
		</ol>

    	<ol>
    		<?php _e("To use variation control use the following trigger text:", "WUSPSC"); ?><br />
    		<strong>[wp_cart:<?php _e("PRODUCT-NAME", "WUSPSC"); ?>:price:<?php _e("PRODUCT-PRICE", "WUSPSC"); ?>:var1[<?php _e("VARIATION-NAME", "WUSPSC"); ?>|<?php _e("VARIATION1", "WUSPSC"); ?>|<?php _e("VARIATION2", "WUSPSC"); ?>|<?php _e("VARIATION3", "WUSPSC"); ?>]:end]</strong><br />
    		<blockquote><?php _e("eg.", "WUSPSC"); ?> [wp_cart:<?php _e("Test Product", "WUSPSC"); ?>:price:15:var1[Size|Small|Medium|Large]:end]</blockquote>
    	</ol>

    	<ol>
    		<?php _e("To use variation control with shipping use the following trigger text:", "WUSPSC"); ?><br />
    		<strong>[wp_cart:<?php _e("PRODUCT-NAME", "WUSPSC"); ?>:price:<?php _e("PRODUCT-PRICE", "WUSPSC"); ?>:shipping:<?php _e("SHIPPING-COST", "WUSPSC"); ?>:var1[<?php _e("VARIATION-NAME", "WUSPSC"); ?>|<?php _e("VARIATION1", "WUSPSC"); ?>|<?php _e("VARIATION2", "WUSPSC"); ?>|<?php _e("VARIATION3", "WUSPSC"); ?>]:end]</strong><br />
    		<blockquote><?php _e("eg.", "WUSPSC"); ?> [wp_cart:<?php _e("Test Product", "WUSPSC"); ?>:price:15:shipping:2:var1[<?php _e("Size|Small|Medium|Large", "WUSPSC"); ?>]:end]</blockquote>
    	</ol>

		<ol>
			<?php _e("To use multiple variation option use the following trigger text:", "WUSPSC"); ?><br />
			<strong>[wp_cart:<?php _e("PRODUCT-NAME", "WUSPSC"); ?>:price:<?php _e("PRODUCT-PRICE", "WUSPSC"); ?>:var1[<?php _e("VARIATION-NAME", "WUSPSC"); ?>|<?php _e("VARIATION1", "WUSPSC"); ?>|<?php _e("VARIATION2", "WUSPSC"); ?>|<?php _e("VARIATION3", "WUSPSC"); ?>]:var2[<?php _e("VARIATION-NAME", "WUSPSC"); ?>|<?php _e("VARIATION1", "WUSPSC"); ?>|<?php _e("VARIATION2", "WUSPSC"); ?>]:end]</strong><br />
			<blockquote><?php _e("eg.", "WUSPSC"); ?> [wp_cart:<?php _e("Test Product", "WUSPSC"); ?>:price:15:shipping:2:var1[<?php _e("Size|Small|Medium|Large", "WUSPSC"); ?>]:var2[<?php _e("Color|Red|Green", "WUSPSC"); ?>]:end]</blockquote>
		</ol>

    </ul>
    </p>
	<p><h4><?php _e("3. To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode", "WUSPSC"); ?></h4>
		<blockquote><blockquote>
			<?php _e("To display checkout to a post or page, simply add the shortcode", "WUSPSC"); ?> <strong>[show_wp_shopping_cart]</strong><br />
			<?php _e("Or use the sidebar widget to add the shopping cart to the sidebar.", "WUSPSC"); ?>
		</blockquote></blockquote>
	</p>
	<p></p>
    </fieldset>

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />    
 	<?php
echo '
	<div class="postbox">
	<h3><label for="title">'.(__("WP Ultra Simple Paypal Shopping Cart Settings", "WUSPSC")).'</label></h3>
	<div class="inside">';

echo '
<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Paypal Email Address", "WUSPSC")).'</th>
<td><input type="text" name="cart_paypal_email" value="'.$defaultEmail.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Paypal Sandbox (cart is in test)", "WUSPSC")).'</th>
<td>Test: <input type="radio" name="is_sandbox" value="1" '.$defaultSandboxChecked1.'/>&nbsp;Production: <input type="radio" name="is_sandbox" value="0" '.$defaultSandboxChecked2.'/><br /> You must open a free developer account to use sandbox for your tests before go live. Go to <a href="https://developer.paypal.com/">https://developer.paypal.com/</a>, register and connect.</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Shopping Cart title", "WUSPSC")).'</th>
<td><input type="text" name="wp_cart_title" value="'.$title.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Text/Image to Show When Cart Empty", "WUSPSC")).'</th>
<td><input type="text" name="wp_cart_empty_text" value="'.$emptyCartText.'" size="60" /><br />'.(__("You can either enter plain text or the URL of an image that you want to show when the shopping cart is empty", "WUSPSC")).'</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency", "WUSPSC")).'</th>
<td><input type="text" name="cart_payment_currency" value="'.$defaultCurrency.'" size="6" /> ('.(__("e.g.", "WUSPSC")).' USD, EUR, GBP, AUD)</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency Symbol", "WUSPSC")).'</th>
<td><input type="text" name="cart_currency_symbol" value="'.$defaultSymbol.'" size="2" style="width: 1.5em;" /> ('.(__("e.g.", "WUSPSC")).' $, &#163;, &#8364;) 
</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency display", "WUSPSC")).'</th>
<td>Is the currency symbol is displayed befor or after the price ? <input type="radio" name="cart_currency_symbol_order" value="1" '.$defaultSymbolOrderChecked1.'/> Before or <input type="radio" name="cart_currency_symbol_order" value="2" '.$defaultSymbolOrderChecked2.'/> After
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Base Shipping Cost", "WUSPSC")).'</th>
<td><input type="text" name="cart_base_shipping_cost" value="'.$baseShipping.'" size="5" /> <br />'.(__("This is the base shipping cost that will be added to the total of individual products shipping cost. Put 0 if you do not want to charge shipping cost or use base shipping cost.", "WUSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Free Shipping for Orders Over", "WUSPSC")).'</th>
<td><input type="text" name="cart_free_shipping_threshold" value="'.$cart_free_shipping_threshold.'" size="5" /> <br />'.(__("When a customer orders more than this amount he/she will get free shipping. Leave empty if you do not want to use it.", "WUSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Must Collect Shipping Address on PayPal", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_collect_address" value="1" '.$wp_shopping_cart_collect_address.' /><br />'.(__("If checked the customer will be forced to enter a shipping address on PayPal when checking out.", "WUSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Use PayPal Profile Based Shipping", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_use_profile_shipping" value="1" '.$wp_shopping_cart_use_profile_shipping.' /><br />'.(__("Check this if you want to use", "WUSPSC")).' <a href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_ProfileAndTools#id08A9EF00IQY" target="_blank">'.(__("PayPal profile based shipping", "WUSPSC")).'</a>. '.(__("Using this will ignore any other shipping options that you have specified in this plugin.", "WUSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Add to Cart button text or Image", "WUSPSC")).'</th>
<td><input type="text" name="addToCartButtonName" value="'.$addcart.'" size="100" /><br />'.(__("To use a customized image as the button simply enter the URL of the image file.", "WUSPSC")).' '.(__("e.g.", "WUSPSC")).' http://www.your-domain.com/wp-content/plugins/wordpress-paypal-shopping-cart/images/buy_now_button.png</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Cart button class style", "WUSPSC")).'</th>
<td><input type="text" name="add_cartstyle" value="'.$add_cartstyle.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Subtotal text", "WUSPSC")).'</th>
<td><input type="text" name="subtotal_text" value="'.$subtotal_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Shipping text", "WUSPSC")).'</th>
<td><input type="text" name="shipping_text" value="'.$shipping_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Total text", "WUSPSC")).'</th>
<td><input type="text" name="total_text" value="'.$total_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Item name text", "WUSPSC")).'</th>
<td><input type="text" name="item_name_text" value="'.$item_name_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Quantity text", "WUSPSC")).'</th>
<td><input type="text" name="qualtity_text" value="'.$qualtity_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Price text", "WUSPSC")).'</th>
<td><input type="text" name="price_text" value="'.$price_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Remove text", "WUSPSC")).'</th>
<td><input type="text" name="remove_text" value="'.$remove_text.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Products page URL title", "WUSPSC")).'</th>
<td><input type="text" name="wp_cart_visit_shop_text" value="'.$wp_cart_visit_shop_text.'" size="100" /></td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Return URL", "WUSPSC")).'</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.$return_url.'" size="100" /><br />'.(__("This is the URL the customer will be redirected to after a successful payment", "WUSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Products Page URL", "WUSPSC")).'</th>
<td><input type="text" name="cart_products_page_url" value="'.$cart_products_page_url.'" size="100" /><br />'.(__("This is the URL of your products page if you have any. If used, the shopping cart widget will display a link to this page when cart is empty", "WUSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Automatic redirection to checkout page", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_auto_redirect_to_checkout_page" value="1" '.$wp_shopping_cart_auto_redirect_to_checkout_page.' />
 '.(__("Checkout Page URL", "WUSPSC")).': <input type="text" name="cart_checkout_page_url" value="'.$cart_checkout_page_url.'" size="60" />
<br />'.(__("If checked the visitor will be redirected to the Checkout page after a product is added to the cart. You must enter a URL in the Checkout Page URL field for this to work.", "WUSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Reset Cart After Redirection to Return Page", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_reset_after_redirection_to_return_page" value="1" '.$wp_shopping_cart_reset_after_redirection_to_return_page.' />
<br />'.(__("If checked the shopping cart will be reset when the customer lands on the return URL (Thank You) page.", "WUSPSC")).'</td>
</tr>
</table>


<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Hide Shopping Cart Image", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_image_hide" value="1" '.$wp_cart_image_hide.' /><br />'.(__("If ticked the shopping cart image will not be shown.", "WUSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use WP Affiliate Platform", "WUSPSC")).'</th>
<td><input type="checkbox" name="wp_use_aff_platform" value="1" '.$wp_use_aff_platform.' />
<br />'.(__("Check this if using with the", "WUSPSC")).' <a href="http://tipsandtricks-hq.com/?p=1474" target="_blank">Ruhul Amin WP Affiliate Platform plugin</a>. '.(__("This plugin lets you run your own affiliate campaign/program and allows you to reward (pay commission) your affiliates for referred sales", "WUSPSC")).'</td>
</tr>
</table>
</div></div>
    <div class="submit">
        <input type="submit" name="info_update" value="'.(__("Update Options &raquo;", "WUSPSC")).'" />
    </div>						
 </form>
 ';
    echo (__("Like the WP Ultra Simple Paypal Shopping Cart Plugin?", "WUSPSC")).' <a href="http://wordpress.org/extend/plugins/wordpress-ultra-simple-paypal-shopping-cart" target="_blank">'.(__("Give it a good rating", "WUSPSC")).'</a>'; 
}

function wp_cart_options()
{
     echo '<div class="wrap"><h2>'.(__("WP Ultra simple Paypal Cart Options", "WUSPSC")).'</h2>';
     echo '<div id="poststuff"><div id="post-body">';
     show_wp_cart_options_page();
     echo '</div></div>';
     echo '</div>';
}

// Display The Options Page
function wp_cart_options_page () 
{
     add_options_page(__("WP Ultra simple Paypal Cart", "WUSPSC"), __("Ultra simple Cart", "WUSPSC"), 'manage_options', __FILE__, 'wp_cart_options');  
}

function show_wp_paypal_shopping_cart_widget($args)
{
	extract($args);
	
	echo $before_widget;
	
	if (cart_not_empty()) {	
		$cart_title = get_option('wp_cart_title');
		if (empty($cart_title)) $cart_title = __("Shopping Cart", "WUSPSC");
		echo $before_title . $cart_title . $after_title; 
		echo print_wp_shopping_cart();
	}
	
    echo $after_widget;
}

function wp_paypal_shopping_cart_widget_control()
{
    ?>
    <p>
    <?php _e("Set the Plugin Settings from the Settings menu", "WUSPSC"); ?>
    </p>
    <?php
}

function widget_wp_paypal_shopping_cart_init()
{	
    $widget_options = array('classname' => 'widget_wp_paypal_shopping_cart', 'description' => __("Display WP Ultra Simple Paypal Shopping Cart.", "WUSPSC") );
    wp_register_sidebar_widget('wp_paypal_shopping_cart_widgets', __("WP Ultra Simple Paypal Shopping Cart", "WUSPSC"), 'show_wp_paypal_shopping_cart_widget', $widget_options);
    wp_register_widget_control('wp_paypal_shopping_cart_widgets', __("WP Ultra Simple Paypal Shopping Cart", "WUSPSC"), 'wp_paypal_shopping_cart_widget_control' );
}

function wp_cart_css()
{
    echo '<link type="text/css" rel="stylesheet" href="'.WP_CART_URL.'/wp_shopping_cart_style.css" />'."\n";
}

// Add the settings link
function wp_ultra_simple_cart_add_settings_link($links, $file) 
{
	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="options-general.php?page='.dirname(plugin_basename(__FILE__)).'/wp_shopping_cart.php">'.(__("Settings", "WUSPSC")).'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'wp_ultra_simple_cart_add_settings_link', 10, 2 );

// Insert the options page to the admin menu
add_action('admin_menu','wp_cart_options_page');
add_action('init', 'widget_wp_paypal_shopping_cart_init');
//add_filter('the_content', 'print_wp_cart_button',11);
//add_filter('the_content', 'print_wp_cart_button_new',11);
add_filter('the_content', 'print_wp_cart_action',11);

add_filter('the_content', 'shopping_cart_show');

add_shortcode('show_wp_shopping_cart', 'show_wp_shopping_cart_handler');

add_shortcode('always_show_wp_shopping_cart', 'always_show_cart_handler');

add_action('wp_head', 'wp_cart_css');
add_action('wp_head', 'wp_cart_add_read_form_javascript');
?>