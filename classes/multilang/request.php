<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Multilang module request class
 * From the module https://github.com/GeertDD/kohana-lang
 */

class Multilang_Request extends Kohana_Request {

	/**
	 * @var string request language code
	 */
	static public $lang = NULL;

	/**	
	 *
	 * Extension of the request factory method. If none given, the URI will
	 * be automatically detected. If the URI contains no language segment and
	 * we don't hide the default language, the user will be redirected to the 
	 * same URI with the default language prepended.
	 * If the URI does contain a language segment, I18n and locale will be set and
	 * a cookie with the current language aswell.
	 *
	 * @param   string   URI of the request
	 * @param	Kohana_Cache cache object
	 * @param   array   $injected_routes an array of routes to use, for testing
	 * @return  Request
	 */
	public static function factory($uri = TRUE, Cache $cache = NULL, $injected_routes = array())
	{
		
		if(!Kohana::$is_cli)
		{
			// If we don't hide the default language, we must look for a language code for the root uri
			if(Request::detect_uri() === '' && Kohana::config('multilang.auto_detect') && $uri === TRUE)
			{			
				$lang = Multilang::find_user_language();
				if(!Kohana::config('multilang.hide_default') || $lang != Kohana::config('multilang.default'))
				{
					// Use the default server protocol
					$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';

					// Redirect to the root URI, but with language prepended
					header($protocol.' 302 Found');
					header('Location: '.URL::base(TRUE, TRUE).$lang.'/');				
					exit;
				}
			}		
		}		
		
		$request = parent::factory($uri, $cache, $injected_routes);
		
		// If the default language is hidden or there is no language, we manually set it to default	
		if(Kohana::config('multilang.hide_default') && $request->param('lang') === NULL || $request->route()->lang === NULL)
		{
			Request::$lang = Kohana::config('multilang.default');
		}
		else
		{
			Request::$lang = $request->param('lang');
		}		

		Multilang::init();
		return $request;
	}

	
	
	
	/**
	 * ONLY REMOVE THE FRONT SLASHES FROM THE URI
	 */
	public function __construct($uri, Cache $cache = NULL, $injected_routes = array())
	{
		// Remove the front slashes
		$uri = ltrim($uri, '/');
		
		// Initialise the header
		$this->_header = new HTTP_Header(array());

		// Assign injected routes
		$this->_injected_routes = $injected_routes;

		// Cleanse query parameters from URI (faster that parse_url())
		$split_uri = explode('?', $uri);
		$uri = array_shift($split_uri);

		// Initial request has global $_GET already applied
		if (Request::$initial !== NULL)
		{
			if ($split_uri)
			{
				parse_str($split_uri[0], $this->_get);
			}
		}

		// Detect protocol (if present)
		// Always default to an internal request if we don't have an initial.
		// This prevents the default index.php from being able to proxy
		// external pages.
		if (Request::$initial === NULL OR strpos($uri, '://') === FALSE)
		{
			$processed_uri = Request::process_uri($uri, $this->_injected_routes);

			if ($processed_uri === NULL)
			{
				throw new HTTP_Exception_404('Unable to find a route to match the URI: :uri', array(
					':uri' => $uri,
				));
			}

			// Store the URI
			$this->_uri = $uri;

			// Store the matching route
			$this->_route = $processed_uri['route'];
			$params = $processed_uri['params'];

			// Is this route external?
			$this->_external = $this->_route->is_external();

			if (isset($params['directory']))
			{
				// Controllers are in a sub-directory
				$this->_directory = $params['directory'];
			}

			// Store the controller
			$this->_controller = $params['controller'];

			if (isset($params['action']))
			{
				// Store the action
				$this->_action = $params['action'];
			}
			else
			{
				// Use the default action
				$this->_action = Route::$default_action;
			}

			// These are accessible as public vars and can be overloaded
			unset($params['controller'], $params['action'], $params['directory']);

			// Params cannot be changed once matched
			$this->_params = $params;

			// Apply the client
			$this->_client = new Request_Client_Internal(array('cache' => $cache));
		}
		else
		{
			// Create a route
			$this->_route = new Route($uri);

			// Store the URI
			$this->_uri = $uri;

			// Set external state
			$this->_external = TRUE;

			// Setup the client
			$this->_client = new Request_Client_External(array('cache' => $cache));
		}
	}


}