# Kohana Multilang Module 2.0

!!! NEW VERSION 2.0 !!!

Multilingual module for Kohana PHP Framework, version 3.1

## Features

* Language segment in uri. Can be optional for the default language
* Works with normal routes
* Custom routes for each language
* Auto language detection for the homepage (headers then cookie)
* Language selector menu

## Usage

### Configuration

	return array(
		'default'		=> 'en',	// The default language code
		'cookie'		=> 'lang',	// The cookie name
		'hide_default'	=> FALSE,	// Hide the language code for the default language
		'auto_detect'	=> TRUE,	// Auto detect the user language on the homepage
		/**
		 * The allowed languages
		 * For each language, you need to give a code (2-5 chars) for the key,
		 * the 5 letters i18n language code, the locale and the label for the auto generated language selector menu.
		 */
		'languages'		=>	array( 
			/*
			'en'		=> array(
				'i18n'		=> 'en_US',
				'locale'    => array('en_US.utf-8'),
				'label'		=> 'english',
			),
			'fr'		=>	array(
				'i18n'		=> 'fr_FR',
				'locale'    => array('fr_FR.utf-8'),
				'label'		=> 'français',
			),
			'de'		=>	array(
				'i18n'		=> 'de_DE',
				'locale'    => array('de_DE.utf-8'),
				'label'		=> 'deutsch',
			),
			 */
		),
	);
	
The default route is special and an example is provided in the init.php file of this module. It is recommended to comment it there and copy it in your bootstrap.

### Example

If you try to access `http://www.domain.tld/`, the module will redirect it to `http://www.domain.tld/en/` if the hide_default option is set to FALSE.

Let's say we have a product page, with kohana 3 we'd have something like : 
	
Route::set('product.details', 'products/<product_id>-<product_slug>', array(
	'product_id'		=> '[0-9]+',
	'product_slug'		=> '.+',
))->defaults(array(
	'controller'		=> 'product',
	'action'			=> 'details',
	'product_id'		=> NULL,
	'product_slug'		=> '',
));


	
You can access `http://www.domain.tld/products/12-my-product` with this one.
Now, I want this url in french too, like that: `http://www.domain.tld/fr/products/12-my-product`.
You can use the `Routes` object (notice the S at the end) to set multiple routes for each language.
Let's take a look:

	Routes::set('product.details', array(
		'en' => 'products/<product_id>-<product_slug>',
		'fr' => 'produits/<product_id>-<product_slug>',
	), array(
		'product_id'		=> '[0-9]+',
		'product_slug'		=> '.+',
	))->defaults(array(
		'controller'		=> 'product',
		'action'			=> 'details',
		'product_id'		=> NULL,
		'product_slug'		=> '',
	));
	
This creates 2 routes named `en.products.details` and `fr.products.details`. The default language (english here) is required. If we have a third language like german with `de`, it will use the default uri, english here.
It is highly recommanded to use reverse routing! With `Route::get('product/details')->uri(array('product_id' => 12, 'product_slug' => 'my_product'))`, you'll get the complete uri with the current user language code. To get another language, just pass a second parameter to `Route::get('product/details', 'en')`.For the routes than dont need any language code, you can keep the normal kohana syntax.
If you wanna create a route for only one language, you're gonna have to pass a 4th parameter:

	Route::set('product.made-in-france', 'produits/fabrique-en-france', NULL, 'fr')
	->defaults(array(
		'controller'		=> 'product',
		'action'			=> 'made_in_france',		
	));

And then to get it `Route::get('fr.product.made-in-france')` or `Route::get('product.made-in-france', 'fr)`.

The default route is particular and its declaration is set in init.php. Since it doesnt need any translations and has a different behaviour (we wanna keep the trailing slash), we create a custom route that alows all the languages.


### Language selector menu

`Multilang::selector($current)` returns a menu to select the language. It will keep the same page if routes are available for the other languages. The `current` parameter adds the current language in the menu.
You can change the view file `multilang/selector.php`.

### Misc	

To get the current language, you can use `Request::$lang`.
	
### Input

If you have any suggestions, found a bug or anything, feel free to share.


### How it works

We change the Request to force the detection of a language on the site root. Then if a route is found, we get the language from it with its 'lang' parameter and we initialize.
Each route created with a language code gets another parameter: `lang`. A uri like `products/details` will become `<lang>/products/details`.
But since every multilingual route is unique (except default), the regex part allows only one language code. So we have `array('lang' => 'en')` instead of having something like `array('lang' => '(en|fr|de')`. We could have directly the language code in the uri like `en/products/details` but the lang parameter allows us to easily retrieve the route language and works better with the default route.