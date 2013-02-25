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
		$config = Kohana::$config->load('multilang');
		
		if(!$config->hide_default || $config->default != $lang)
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
		elseif(isset(Route::$_routes[Kohana::$config->load('multilang')->default.'.'.$name])) {
			$name = $config->default.'.'.$name;
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
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   $params URI parameters
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
		
		// Start with the routed URI
		$uri = $this->_uri;

		if (strpos($uri, '<') === FALSE AND strpos($uri, '(') === FALSE)
		{
			// This is a static route, no need to replace anything

			if ( ! $this->is_external())
				return $uri;

			// If the localhost setting does not have a protocol
			if (strpos($this->_defaults['host'], '://') === FALSE)
			{
				// Use the default defined protocol
				$params['host'] = Route::$default_protocol.$this->_defaults['host'];
			}
			else
			{
				// Use the supplied host with protocol
				$params['host'] = $this->_defaults['host'];
			}

			// Compile the final uri and return it
			return rtrim($params['host'], '/').'/'.$uri;
		}

		// Keep track of whether an optional param was replaced
		$provided_optional = FALSE;

		while (preg_match('#\([^()]++\)#', $uri, $match))
		{

			// Search for the matched value
			$search = $match[0];

			// Remove the parenthesis from the match as the replace
			$replace = substr($match[0], 1, -1);

			while (preg_match('#'.Route::REGEX_KEY.'#', $replace, $match))
			{
				list($key, $param) = $match;

				if (isset($params[$param]) AND $params[$param] !== Arr::get($this->_defaults, $param))
				{
					// Future optional params should be required
					$provided_optional = TRUE;

					// Replace the key with the parameter value
					$replace = str_replace($key, $params[$param], $replace);
				}
				elseif ($provided_optional)
				{
					// Look for a default
					if (isset($this->_defaults[$param]))
					{
						$replace = str_replace($key, $this->_defaults[$param], $replace);
					}
					else
					{
						// Ungrouped parameters are required
						throw new Kohana_Exception('Required route parameter not passed: :param', array(
							':param' => $param,
						));
					}
				}
				else
				{
					// This group has missing parameters
					$replace = '';
					break;
				}
			}

			// Replace the group in the URI
			$uri = str_replace($search, $replace, $uri);
		}

		while (preg_match('#'.Route::REGEX_KEY.'#', $uri, $match))
		{
			list($key, $param) = $match;

			if ( ! isset($params[$param]))
			{
				// Look for a default
				if (isset($this->_defaults[$param]))
				{
					$params[$param] = $this->_defaults[$param];
				}
				else
				{
					// Ungrouped parameters are required
					throw new Kohana_Exception('Required route parameter not passed: :param', array(
						':param' => $param,
					));
				}
			}

			$uri = str_replace($key, $params[$param], $uri);
		}

		// Trim all extra slashes from the URI
		//$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));
		$uri = preg_replace('#//+#', '/', $uri);

		if ($this->is_external())
		{
			// Need to add the host to the URI
			$host = $this->_defaults['host'];

			if (strpos($host, '://') === FALSE)
			{
				// Use the default defined protocol
				$host = Route::$default_protocol.$host;
			}

			// Clean up the host and prepend it to the URI
			$uri = rtrim($host, '/').'/'.$uri;
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
	 * We don't want to remove the trailing slash.
	 */
	public function matches(Request $request)
	{
		// Get the URI from the Request
		//$uri = trim($request->uri(), '/');
		$uri = ltrim($request->uri(), '/');

		if ( ! preg_match($this->_route_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value)
		{
			if ( ! isset($params[$key]) OR $params[$key] === '')
			{
				// Set default values for any key that was not matched
				$params[$key] = $value;
			}
		}

		if ( ! empty($params['controller']))
		{
			// PSR-0: Replace underscores with spaces, run ucwords, then replace underscore
			$params['controller'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['controller'])));
		}

		if ( ! empty($params['directory']))
		{
			// PSR-0: Replace underscores with spaces, run ucwords, then replace underscore
			$params['directory'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['directory'])));
		}

		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				// Execute the filter giving it the route, params, and request
				$return = call_user_func($callback, $this, $params, $request);

				if ($return === FALSE)
				{
					// Filter has aborted the match
					return FALSE;
				}
				elseif (is_array($return))
				{
					// Filter has modified the parameters
					$params = $return;
				}
			}
		}

		return $params;
	}
	
}