=== WordPress Ultra Simple Paypal Shopping Cart ===
Contributors: Mike Castro Demaria
Donate link: http://www.ultra-prod.com/?p=86
Tags: WordPress shopping cart, Paypal shopping cart, online shop, shopping cart, wordpress ecommerce, sell products, e-commerce, PayPal cart widget
Requires at least: 2.6
Tested up to: 3.2.1
Stable tag:4.2.2

Ultra Simple WordPress Paypal Shopping Cart Plugin is a very easy to use plugin. Great for selling products or service online, with Paypal handling the transaction, in one click. All that is required is placing a simple shortcode on any post or page or in theme.

== Description ==

WordPress Ultra Simple Paypal Shopping Cart allows you to add an 'Add to Cart' button on any posts or pages. It also allows you to add/display the shopping cart on any post or page or sidebar easily.
The shopping cart shows the user what they currently have in the cart and allows them to remove the items. You can also add a form between the cart validation and the paypal submit if you choose a 3 step process.

WP Ultra simple Paypal Cart Plugin, interfaces with the Paypal sandbox to allow for testing. You need to set up a developer test account for the sandbox at Paypal. Simply put you paypal sandbox merchant
email address in the admin panel, and click the 'test' radio button.
Other new features include, Price Variations, interface text's personalization, CSS call for the 'Add to cart button' and many other improvements, including bug correction too.

This plugin customisable in the plugin admin and 100% customisable by CSS.
To style the 'add to cart' button use cart button class style in the admin. Eg. 

For screenshots, detailed documentation, support and updates, please visit [Ultra Prod Forum](http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3.0/ "go to Ultra Prod forum")

This plug-in is based on the Ruhul Amin's Simple Paypal Shopping Cart

== Usage ==

* To add the ‘Add to Cart’ button on you theme’s template files, use <?php echo print_wp_cart_button_for_product(‘PRODUCT-NAME’, PRODUCT-PRICE); ?> . Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
* To display the numbers of items in cart use <?php echo wpusc_cart_item_qty(); ?> . The string display are set in the plugin's settings.
* To add the 'Add to Cart' button simply add the trigger text [wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:end] to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
* To add the 'Add to Cart' button on the sidebar use the widget.
* To add the 'Add to Cart' button on you theme's template files, use the following function:  . Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price. You can use price and shipping variation too.
* To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode [show_wp_shopping_cart] to a post or page or use the sidebar widget to add the shopping cart to the sidebar. The shopping cart will only be visible in a post or page when a customer adds a product.

* You must use [validate_wp_shopping_cart] shortcode on another page if you want to use the 3 steps process.
1. Create a page with the shortcode [validate_wp_shopping_cart]
2. Create a page with your form (Cform2 is the better choice) and do the following configuration to your form:
3. Uncheck "Ajax enabled",
4. Go to Form Settings, Core Form Admin / Email Options section, Redirect option, and check enable alternative success page (redirect), plus past your final page's URL (the page who contain [show_wp_shopping_cart] tag)
5. Create a page with the shortcode [show_wp_shopping_cart]

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

3. To use price variation and shipping variation use the following trigger text (use dot for price cents separator please)
[wp_cart:PRODUCT-NAME:price:[VARIATION-NAME|VARIATION-LABEL1,VARIATION-PRICE1|VARIATION-LABEL2,VARIATION-PRICE2|VARIATION-LABEL3,VARIATION-PRICE3]:shipping:[SHIPPING-NAME|VARIATION-LABEL1,VARIATION-PRICE1|VARIATION-LABEL2,VARIATION-PRICE2|VARIATION-LABEL3,VARIATION-PRICE3]:end]
eg. [wp_cart:Demo Product 1:price:[Size|Small,1.10|Medium,2.10|Large,3.10]:shipping:[Shipping|normal,1.5|fast,10.5]:end]

4. To use multiple variation option use the following trigger text:
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:var2[VARIATION-NAME|VARIATION1|VARIATION2]:end]
eg. [wp_cart:Demo Product 1:price:15:shipping:2:var1[Size|Small|Medium|Large]:var2[Color|Red|Green]:end]

Keyword list : 
* price eg. 45.50 or a list like price:[Size|Small,1.10|Medium,2.10|Large,3.10], 
* shipping : eg. 3.50 or a list like price:[Shipping type|regular mail,1.10|express mail,2.10|priority mail,3.10], 
* var1 : eg. var1[Size|Small|Medium|Large] , 
* var2 : eg. var2[Color|Red|Green]
* var3, etc.

== Installation ==

1. Unzip and Upload the folder 'wordpress-paypal-shopping-cart' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options eg. your email, Shopping Cart name, Return URL etc.
4. Use the trigger text to add a product to a post or page where u want it to appear.

== Frequently Asked Questions ==

1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have a shopping cart? Yes.
3. Can the shopping cart be added to a checkout page? Yes.
4. Does this plugin have multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.
7. What is the 3 step process? 1) add items to cart 2) collect buyer information on a form 3) process transaction via paypal 
    (2 step process is steps 1 and 3 above)


== Screenshots ==

* screenshot-1.png screenshot-2.png screenshot-3.png screenshot-4.png screenshot-5.png
* Visit [the plugin site](http://www.ultra-prod.com/?p=86) for more screenshots.
* Support [Ultra Prod forum](http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3.0/ "Ultra Prod forum").


== Changelog ==

* More Changelog can be found at [Ultra Prod forum](http://www.ultra-prod.com/developpement-support/wp-ultra-simple-paypal-shopping-cart-group3/annoucements-and-updates-forum7/ "Ulra Prod forum").
= 4.0.0 =
* base improvement
= 4.0.2 =
* bugs correction : Items with single price display in cart with zero value (thanks Lance) and button code display
= 4.1.0 =
* Add feature : Unlimited variations, shipping variation
* Add translation : Spanish translated (automatic, please correcte it if you can).
= 4.1.1 =
* Bug correction : submit new Quantity dont work correctly when you use variation of the price (thanks polkan)
* Bug correction : text "Your Cart is Empty" will display on the Checkout Page (thanks dannidar)
= 4.1.2 =
* Compatibility: rename differents functions to avoid duplicate function name in theme or other plugin.
* Update : label and named class for input in cart form.
* Add : About tab, if you like my work ;-) .
= 4.1.2.1 =
* Bug correction : Embed code for template bug, price is not accepted (thanks iamfabian)
= 4.1.3 =
* Add feature : Checkbox to enable or disable empty cart message on page/post and widget
* Add feature : Checkbox to enable or disable items in cart count cart message on page/post and widget
* Bug correction : Settings link on plugin list was wrong
* Bug correction : Images on about was not in the package
= 4.2.0 =
* Add feature : Add the posibility to propose a 3 steps Paypal redirect (Step-1 cart's validation, Step-2 use and form page who redirect to page 3,Step-3 cart paypal validation and redirect.)
* Add feature : Add [validate_wp_shopping_cart] shortcode for step1 cart,  [show_wp_shopping_cart] is always available for 1 step cart or final step cart.
* Enhancement : rename functions
* Enhancement : Add class to variation select menu
* Enhancement : Add locate paypal button in EN, FR, DE, IT, ES
* Cleaning : Clean and add class / id. Removing style tag in the html code
= 4.2.1 =
* Add feature : Add a new option switch, display products URL in cart.
* Bug correction : Usage and redme doc error, show_wp_shopping_cart instead show_wpus_shopping_cart (thanks simstace)
= 4.2.2 =
* Add feature : Add a new function to display items count in template wpusc_cart_item_qty() .
* Add feature : Paypal button use default language or can be customized usin wp_cart_xpcheckout_button class.
* Enhancement : readme is  rewrited to add clarity (thanks jr-whs)
