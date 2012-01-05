<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang module route class
 */

class Multilang_Route extends Kohana_Route {

	public $lang = NULL;

	/**
	 * Altered method to allow a language
	 * 	 *
	 * @param   string   route name
	 * @param   mixed   URI pattern or Array of URI patterns or a lambda/callback function
	 * @param   array   regex patterns for route keys
	 * @param	mixed  route lang code OR FALSE if you wanna prevent the language code from being added
	 * @return  Route
	 */
	static public function set($name, $uri_callback = NULL, $regex = NULL, $lang = NULL)
	{		
		if(!Kohana::$config->load('multilang.hide_default') || Kohana::$config->load('multilang.default') != $lang)
		{
			if($lang !== NULL)
			{
				$uri_callback = '<lang>/'.$uri_callback;		
				$regex['lang'] = $lang;			
			}
		}
		
		if($lang !== NULL)
		{
			$name = $lang.'.'.$name;
		}		
		
		return Route::$_routes[$name] = new Route($uri_callback, $regex, $lang);		
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
			
		} // then the default language
		elseif(isset(Route::$_routes[Kohana::$config->load('multilang.default').'.'.$name])) {
			$name = Kohana::$config->load('multilang.default').'.'.$name;
		}
		// And if we don't have any for this language, it means that route is neither defined nor multilingual		
		return parent::get($name);
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
		$this->lang = $lang;
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
		// We define the language if required
		if($this->lang !== NULL)
		{
			$params['lang'] = ($lang === NULL ? $this->lang : $lang);
		}		
		
		$uri = parent::uri($params);
		// If it's the default route, we add a trailing slash
		if(Route::name($this) === 'default')
		{
			$uri .= '/';
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

	
}