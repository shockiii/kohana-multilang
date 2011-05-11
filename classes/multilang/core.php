<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang core class
 * From the module https://github.com/GeertDD/kohana-lang
 */

class Multilang_Core {

	static public $lang = '';

	/**
	 * Looks for the best default language available and returns it.
	 * A language cookie and HTTP Accept-Language headers are taken into account.
	 *
	 * @return  string  language key, e.g. "en", "fr", "nl", etc.
	 */
	static public function find_default()
	{
		// Get the list of supported languages
		$langs = (array) Kohana::config('multilang.languages');
		$cookie = Kohana::config('multilang.cookie');
		
		// Look for language cookie first
		if($lang = Cookie::get($cookie))
		{
			// Valid language found in cookie
			if(isset($langs[$lang]))
			{
				return $lang;
			}

			// Delete cookie with unset language
			Cookie::delete($cookie);
		}

		// Parse HTTP Accept-Language headers
		foreach(Request::accept_lang() as $lang => $quality)
		{
			// Return the first language found (the language with the highest quality)
			if(isset($langs[$lang]))
			{
				return $lang;
			}
		}

		// Return the hard-coded default language as final fallback
		return Kohana::config('multilang.default');
	}

	/**
	 * Initialize the config and cookies
	 */
	static public function init()
	{
		// Get the list of supported languages
		$langs = (array) Kohana::config('multilang.languages');

		// Set the language in I18n
		I18n::lang($langs[Request::$lang]['i18n']);

		// Set locale
		setlocale(LC_ALL, $langs[Request::$lang]['locale']);

		$cookie = Kohana::config('multilang.cookie');
		// Update language cookie if needed
		if(Cookie::get($cookie) !== Request::$lang)
		{
			Cookie::set($cookie, Request::$lang);
		}
	}

	/**
	 * Return a language selector menu
	 * @param boolean $current Display the current language or not
	 * @return View
	 */
	static public function selector($current = TRUE)
	{
		$languages = (array) Kohana::config('multilang.languages');

		// get the current route name
		$current_route = Route::name(Request::initial()->route());
		$default_language = Kohana::config('multilang.default');
		
		$params = Request::initial()->param();

		if(strpos($current_route, '.') !== FALSE)
		{
			// Split the route path
			list($lang, $name) = explode('.', $current_route, 2);
		} else {
			$name = $current_route;
		}

		// Create uris for each language
		foreach($languages as $code => &$language)
		{
			if($code == Request::$lang)
			{
				if($current)
				{
					$language['uri'] = FALSE;
				} else {
					unset($languages[$code]);
				}
			} else {				
				
				$language['uri'] = Route::get($name, $code)->uri($params, $code);
			}
		}
		return View::factory('multilang/selector')
			->bind('languages', $languages);
	}
}