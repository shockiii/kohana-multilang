<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang module route class
 */

class Multilang_Route extends Kohana_Route {

	protected $_lang = '';

	static protected $_nolang_routes = array();

	/**
	 * Altered method to allow multiple routes for i18n.
	 * You can pass an array with the language code as the key and the uri as the value.
	 *
	 *		Route::set('homepage', array(
	 *				'en'		=> 'home',
	 *				'fr'		=> 'accueil',
	 *			))->defaults(array(
	 *				'controller'		=> 'homepage',
	 *				'action'			=> 'index',
	 *			));
	 *
	 * Stores a named route and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     Route::set('default', '(<controller>(/<action>(/<id>)))')
	 *         ->defaults(array(
	 *             'controller' => 'welcome',
	 *         ));
	 *
	 * @param   string   route name
	 * @param   mixed   URI pattern or Array of URI patterns or a lambda/callback function
	 * @param   array   regex patterns for route keys
	 * @param	mixed  route lang code OR FALSE if you wanna prevent the language code from being added
	 * @return  Route
	 */
	static public function set($name, $uri_callback = NULL, $regex = NULL, $lang = NULL)
	{
		if($lang)
		{
			$name = $lang.'.'.$name;
		}
		Route::$_routes[$name] = new Route($uri_callback, $regex, $lang);
		if($lang === FALSE)
		{
			Route::$_nolang_routes[$name] = Route::$_routes[$name];
		}
		return Route::$_routes[$name];
	}


	/**
	 * Retrieves a named route.
	 *
	 *     $route = Route::get('default');
	 *
	 * @param   string  route name
	 * @return  Route
	 * @throws  Kohana_Exception
	 */
	static public function get($name, $lang = NULL)
	{
		// We use the current language if none given
		if($lang === NULL)
		{
			$lang = Request::$lang;
		}

		// We first look for a "given_language.name" route.
		if(isset(Route::$_routes[$lang.'.'.$name]))
		{
			$name = $lang.'.'.$name;
			// then the default language
		} elseif(isset(Route::$_routes[Kohana::config('multilang.default').'.'.$name])) {
			$name = Kohana::config('multilang.default').'.'.$name;
		}
		$route = parent::get($name);
		if($route !== NULL)
		{
			$route->_lang = $lang;
		}
		return $route;
	}

	/**
	 * Altered constructor to handle multilingual routes
	 *
	 * Creates a new route. Sets the URI and regular expressions for keys.
	 * Routes should always be created with [Route::set] or they will not
	 * be properly stored.
	 *
	 *     $route = new Route($uri, $regex);
	 *
	 * The $uri parameter can either be a string for basic regex matching or it
	 * can be a valid callback or anonymous function (php 5.3+). If you use a
	 * callback or anonymous function, your method should return an array
	 * containing the proper keys for the route. If you want the route to be
	 * "reversable", you need to return a 'uri' key in the standard syntax.
	 *
	 *     $route = new Route(function($uri)
	 *     {
	 *     	if (list($controller, $action, $param) = explode('/', $uri) AND $controller == 'foo' AND $action == 'bar')
	 *     	{
	 *     		return array(
	 *     			'controller' => 'foobar',
	 *     			'action' => $action,
	 *     			'id' => $param,
	 *     			'uri' => 'foo/bar/<id>.html
	 *     		);
	 *     	}
	 *     });
	 *
	 * @param   mixed    route URI pattern or lambda/callback function
	 * @param   array    key patterns
	 * @param
	 * @return  void
	 * @uses    Route::_compile
	 */
	public function __construct($uri = NULL, array $regex = NULL, $lang = NULL)
	{
		$this->_lang = $lang;
		return parent::__construct($uri, $regex);
	}


	/**
	 * Altered method to handle multilingual uris.
	 * 
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   URI parameters
	 * @param   string $lang a language code
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_Key
	 */
	public function uri(array $params = NULL, $lang = NULL)
	{
		$uri = parent::uri($params);

		// We add the language code if required
		if($this->_lang)
		{
			// We dont use the route language to avoid some issues with routes of different languages having the same pattern
			$lang = ($lang === NULL ? Request::$lang : $lang);
			return $lang.'/'.$uri;
		}
		return $uri;
	}

	/**
	 * Altered method to handle multilingual parameter
	 *
	 * Create a URL from a route name. This is a shortcut for:
	 *
	 *     echo URL::site(Route::get($name)->uri($params), $protocol);
	 *
	 * @param   string   route name
	 * @param   array    URI parameters
	 * @param   mixed   protocol string or boolean, adds protocol and domain
	 * @return  string
	 * @since   3.0.7
	 * @uses    URL::site
	 */
	static public function url($name, array $params = NULL, $protocol = NULL, $lang = NULL)
	{
		// Create a URI with the route and convert it to a URL
		return URL::site(Route::get($name, $lang)->uri($params), $protocol);
	}


	/**
	 * Get all the routes without any language code
	 * @return array
	 */
	static public function nolang_routes()
	{
		return Route::$_nolang_routes;
	}


	/**
	 * Saves or loads the route cache. If your routes will remain the same for
	 * a long period of time, use this to reload the routes from the cache
	 * rather than redefining them on every page load.
	 *
	 * It remakes the nolang_routes array too for language less routes
	 *
	 *     if ( ! Route::cache())
	 *     {
	 *         // Set routes here (or include a file for example)
	 *         Route::cache(TRUE);
	 *     }
	 *
	 * @param   boolean   cache the current routes
	 * @return  void      when saving routes
	 * @return  boolean   when loading routes
	 * @uses    Kohana::cache
	 */
	static public function cache($save = FALSE)
	{
		$return = parent::cache($save);
		if($save !== TRUE && Route::$_routes)
		{
			Route::$_nolang_routes = array();
			foreach(Route::$_routes as $name => $route)
			{
				if($route->_lang === FALSE)
				{
					Route::$_nolang_routes[$name] = Route::$_routes[$name];
				}
			}
		}
		return $return;
	}

}