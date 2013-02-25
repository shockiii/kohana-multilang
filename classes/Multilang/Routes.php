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
		$config = Kohana::$config->load('multilang');
		$routes = new Routes();
		
		// We add the routes for each language and set their names to lang.name (en.homepage for example).
		// The <lang> segment is also added on the uri if it's not hidden
		
		$default_lang	= $config->default;
		$languages		= $config->languages;
		
		// We first look for the default language uri which is obviously compulsory
		$default_uri = Arr::get($uris, $default_lang);
		if($default_uri === NULL)
		{
			throw new Kohana_Exception('The default language route uri is required for the route: :route', array(':route' => $name));
		}
		else
		{			
			$routes->_routes[$default_lang.'.'.$name] = Route::set($name, $default_uri, $regex, $default_lang);			
		}
		unset($languages[$default_lang]);
		
		// Then we add the routes for all the other languages
		foreach($languages as $lang => $settings)
		{			
			$uri = (Arr::get($uris, $lang) ? $uris[$lang] : $uris[$default_lang]);

			// For the uri, we use the one given or the default one			
			$routes->_routes[$lang.'.'.$name] = Route::set($name, $uri, $regex, $lang);
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