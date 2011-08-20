=== WordPress Ultra Simple Paypal Shopping Cart ===
Contributors: Mike Castro Demaria
Donate link: http://www.ultra-prod.com/?p=86
Tags: WordPress shopping cart, PayPal API, Paypal shopping cart, online shop, shopping cart, wordpress ecommerce, sell digital products
Requires at least: 2.6
Tested up to: 3.2.1
Stable tag:4.0.0

Very easy to use **Ultra Simple WordPress Paypal Shopping Cart** Plugin. Great for selling products or service online in one click from your WordPress site with simple shortcode in any post or page you like.

== Description ==

WordPress Ultra Simple Paypal Shopping Cart allows you to add an 'Add to Cart' button on any posts or pages. It also allows you to add/display the shopping cart on any post or page or sidebar easily.
The shopping cart shows the user what they currently have in the cart and allows them to remove the items.
WP Ultra simple Paypal Cart Plugin,  use PayPal API (you need to create a PayPal account : https://www.paypal.com/fr/mrb/pal=DKBDRZGU62JYC ).
Added different features like PayPal sandbox test, Price Variations, interface text's personalization, CSS call for button and many other improvements and bug correction too.
This plug-in is based on the based on Ruhul Amin's "Simple Paypal Shopping Cart" v3.2.3 http://www.tipsandtricks-hq.com/?p=768) 

For screenshots, detailed documentation, support and updates, please visit:
http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3.0/

== Usage ==
* To add the 'Add to Cart' button simply add the trigger text [wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:end] to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
* To add the 'Add to Cart' button on the sidebar use the widget.
* To add the 'Add to Cart' button on you theme's template files, use the following function: <?php echo print_wp_cart_button_for_product('PRODUCT-NAME', PRODUCT-PRICE); ?> . Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
* To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode [show_wp_shopping_cart] to a post or page or use the sidebar widget to add the shopping cart to the sidebar. The shopping cart will only be visible in a post or page when a customer adds a product.

**Using Shipping**
1. To use per product shipping cost use the following shortcode text in you post/page.
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:shipping:SHIPPING-COST:end]

Using Variation Control
1. To use variation control use the following trigger text
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:end]
eg. [wp_cart:Demo Product 1:price:15:var1[Size|Small|Medium|Large]:end]

2. To use price variation use the following trigger text (use dot for price cents separator please)
[wp_cart:PRODUCT-NAME:price:[VARIATION-NAME|VARIATION-LABEL1,VARIATION-PRICE1|VARIATION-LABEL2,VARIATION-PRICE2|VARIATION-LABEL3,VARIATION-PRICE3]:end]
eg. [wp_cart:Demo Product 1:price:[Size|Small,1.10|Medium,2.10|Large,3.10]:shipping:SHIPPING-COST:end]

3. To use multiple variation option use the following trigger text:
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:var2[VARIATION-NAME|VARIATION1|VARIATION2]:end]
eg. [wp_cart:Demo Product 1:price:15:shipping:2:var1[Size|Small|Medium|Large]:var2[Color|Red|Green]:end]

Keyword list : 
* price eg. 45.50 or a list like price:[Size|Small,1.10|Medium,2.10|Large,3.10], 
* shipping : eg. 3.50 or a list like price:[Size|regular mail,1.10|express mail,2.10|priority mail,3.10], 
* var1 : eg. var1[Size|Small|Medium|Large] , 
* var2 : eg. var2[Color|Red|Green]

== Installation ==

1. Unzip and Upload the folder 'wordpress-paypal-shopping-cart' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options eg. your email, Shopping Cart name, Return URL etc.
4. Use the trigger text to add a product to a post or page where u want it to appear.

== Frequently Asked Questions ==
1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have shopping cart? Yes.
3. Can the shopping cart be added to a checkout page? Yes.
4. Does this plugin has multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.


== Screenshots ==
* Visit the plugin site at http://www.ultra-prod.com/?p=86 for screenshots.
* Support [Ultra Prod forum link](http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3.0/ "Ulra Prod").

== Changelog ==
= 4.0.0 =
* Changelog can be found at the following URL
* http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3/annoucements-and-updates-forum7/

