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
	
This creates 2 routes named en.products.details and fr.products.details. The default language (english here) is required. If we have a third language like "de", it will use "en". The thing is, both url  http://www.domain.tld/fr/products/12-my-product and http://www.domain.tld/en/produits/12-my-product will still work. To make sure this is not an issue, you should use reverse routing everywhere. With Route::get('product/details')->uri(array('product_id' => 12, 'product_slug' => 'my_product')), you'll get the complete uri with the current user language code. To get another language, just pass a second parameter to Route::get('product/details', 'en').

Now, I got a controller that serves CSS files, I obviously don't need any language specified. To prevent the normal behaviour, just pass FALSE as the third parameter to Route::set like this :
	
	Route::set('file.css', 'static/css/<action>.css', array(
		'action'			=> '[^/.]+',
	), FALSE)->defaults(array(
		'controller'		=> 'css',
		'action'			=> NULL,
	));
	
If you access http://www.domain.tld/static/css/custom_page.css, it will not redirect and Route::get('file.css.custom')->uri(array('action' => 'categories')) will not return the uri with a language code.

### Language selector menu

Multilang::selector($current) returns a menu to select the language. It will keep the same page. The "current" parameter adds the current language in the menu or not.
You can change the view file multilang/selector.php.

### Misc	

To access the current language, you can use Request::$lang.
	
### Input

If you have any suggestions, found a bug or anything, feel free to share.


### How it works

See https://github.com/GeertDD/kohana-lang

#### The URI does not contain a language code

If somebody visits "http://www.domain.tld/page", without a language, the best default language will be found and the user will be redirected to the same URL *with* that language prepended. To find the best language, the following elements are taken into account (in this order):

1. a language cookie (set during a previous visit);
2. the HTTP Accept-Language header;
3. a hard-coded default language.

#### The URI contains a language code

1. The language code is chopped off before the request and stored in Request::$lang.
2. "I18n::$lang" is set to the correct target language (from config).
3. The correct locale is set (from config).
4. A cookie with the language code is set.
5. Normal request processing continues.

It is important to be aware that the *language part is completely chopped off* of the URI. When normal request processing continues it, it does so with a URI without language. This means that **your routes must not contain a `<lang>` key**. Also, you can create HMVC subrequests without having to worry about adding the current language to the URI.

The one thing we still need to take care of then, is that any generated URLs should contain the language. An extension of `URL::site` is created for this. A third argument, `$lang`, is added to `URL::site`. By default, the current language is used (`Request::$lang`). You can also provide another language key as a string, or set the argument to `FALSE` to generate a URL without language.
