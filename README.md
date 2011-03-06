# Kohana Multilang Module

Multilingual module for Kohana PHP Framework, version 3.1

Partly based on this module https://github.com/GeertDD/kohana-lang

## Features

* Language segment in uri
* Works with normal routes
* Custom routes for each language (localization or parameters)
* Auto language detection or cookie
* Language selection menu

## Usage

### Configuration

	return array(
		'default'		=> 'en', // The default language code
		'cookie'		=> 'lang', // The cookie name
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

### Example

If you try to access http://www.domain.tld/, the module will redirect it to http://www.domain.tld/en/ for example.

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

	
If you try to access http://www.domain.tld/products/12-my-product, it will redirect to http://www.domain.tld/en/products/12-my-product.
Now, I'm on the same page in french http://www.domain.tld/fr/products/12-my-product, but I'd like to translate it and set "produits" instead of "products". You can use the "Routes" object (notice the S at the end) to set multiple routes for each language.

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
	
This creates 2 routes, the default language (english here) is required. If we have a third language like "de", it will use "en". The thing is, both url  http://www.domain.tld/fr/products/12-my-product and http://www.domain.tld/en/produits/12-my-product will still work. To make sure this is not an issue, you should use reverse routing everywhere. With Route::get('product/details')->uri(array('product_id' => 12, 'product_slug' => 'my_product')), you'll get the complete uri with the current language code. To get another language, just pass a second paramter to Route::get('product/details', 'en').



### Language selector menu


	

To access the current language, you can use Request::$lang.
	
### Input

If you have any suggestions, found a bug or anything, feel free to share.


	
