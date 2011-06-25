<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang module routes class
 */

class Multilang_Routes {

	protected $_routes = array();

	/**
	 * Set routes for each language
	 * You can pass an array with the language code as the key and the uri as the value.
	 *
	 *		Routes::set('homepage', array(
	 *				'en'		=> 'home',
	 *				'fr'		=> 'accueil',
	 *			))->defaults(array(
	 *				'controller'		=> 'homepage',
	 *				'action'			=> 'index',
	 *			));
	 *	
	 * @param   string   route name
	 * @param   array   URI patterns (array of "language code" => "uri")
	 * @param   array    regex patterns for route keys
	 * @return  Routes
	 */
	static public function set($name, $uris = array(), $regex = NULL)
	{
		$routes = new Routes();
		
		// We add the routes for each language and set their names to lang.name (en.homepage for example).
		// The <lang> segment is also added on the uri if it's not hidden
		
		$default_lang	= Kohana::config('multilang.default');
		$languages		= Kohana::config('multilang.languages');
		// We first look for the default language uri which is obviously compulsory
		$default_uri = Arr::get($uris, $default_lang);
		if($default_uri === NULL)
		{
			throw new Kohana_Exception('The default route uri is required for the language :lang', array(':lang' => $default_lang));
		}
		else
		{
			// If we dont hide the default language in the uri
			if(!Kohana::config('multilang.hide_default'))
			{
				$default_uri = '<lang>/'.$default_uri;		
				$regex['lang'] = $default_lang;
			}
			$routes->_routes[$default_lang.'.'.$name] = Route::set($default_lang.'.'.$name, $default_uri, $regex, $default_lang);
			
		}
		unset($languages[$default_lang]);
		
		// Then we add the routes for all the other languages
		foreach($languages as $lang => $settings)
		{			
			$uri = '<lang>/'.(Arr::get($uris, $lang) ? $uris[$lang] : $uris[$default_lang]);
			$regex['lang'] = $lang;

			// For the uri, we use the one given or the default one			
			$routes->_routes[$lang.'.'.$name] = Route::set($lang.'.'.$name, $uri, $regex, $lang);
		}		
		return $routes;
	}


	/**
	 * Set the defaults values for each route
	 * @param array $defaults
	 * @return Multilang_Routes
	 */
	public function defaults(array $defaults = NULL)
	{
		foreach($this->_routes as $route)
		{
			$route->defaults($defaults);
		}
		return $this;
	}


}