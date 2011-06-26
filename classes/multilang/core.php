<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang core class
 * From the module https://github.com/GeertDD/kohana-lang
 */

class Multilang_Core {

	static public $lang = '';

	/**
	 * Looks for the user language.
	 * A language cookie and HTTP Accept-Language headers are taken into account.
	 * 
	 * If the auto detection is disabled, we return the default one
	 *
	 * @return  string  language key, e.g. "en", "fr", "nl", "en_US", "en-us", etc.
	 */
	static public function find_user_language()
	{
		if(Kohana::config('multilang.auto_detect'))
		{
			// Get the list of supported languages
			$languages	= (array) Kohana::config('multilang.languages');
			$cookie		= Kohana::config('multilang.cookie');

			// Look for language cookie first
			if($lang = Cookie::get($cookie))
			{
				// Valid language found in cookie
				if(isset($languages[$lang]))
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
				if(isset($languages[$lang]))
				{
					return $lang;
				}
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

		// Get the current route name
		$current_route = Route::name(Request::initial()->route());		
		
		
		$params = Request::initial()->param();

		if($current_route !== 'default' && strpos($current_route, '.') !== FALSE)
		{
			// Split the route path
			list($lang, $name) = explode('.', $current_route, 2);
		}
		else
		{
			$name = $current_route;
		}

		// Create uris for each language
		foreach($languages as $lang => &$language)
		{				
			// If it's the current language
			if($lang === Request::$lang)
			{
				// We only display it when required
				if($current)
				{
					$selectors[$lang] = '<span class="multilang-selected multilang-'.$lang.'">'.$languages[$lang]['label'].'</span>';
				}				
			}
			else
			{	
				$route = NULL;
				
				// If it's the default route, it's unique and special (like you <3)
				if($current_route === 'default')
				{
					// We juste need to change the language parameter
					$route = Request::initial()->route();					
					$params['lang'] = NULL;
					if(!Kohana::config('multilang.hide_default') || Kohana::config('multilang.default') !== $lang)
					{
						$params['lang'] = $lang;					
					}
					
					
				}
				else
				{	
					if(Arr::get(Route::all(), $lang.'.'.$name))
					{
						$route = Route::get($name, $lang);
					}
				}					

				if($route !== NULL)
				{
					$selectors[$lang] = HTML::anchor($route->uri($params), $languages[$lang]['label'], array('class' => 'multilang-selectable multilang-'.$lang, 'title' => $languages[$lang]['label']));
				}
			}
		}
		
		// We display the menu only if we can select another language for this page
		if(count($selectors) > 1)
		{
			return View::factory('multilang/selector')
			->bind('selectors', $selectors);
		}
		return '';
	}
}