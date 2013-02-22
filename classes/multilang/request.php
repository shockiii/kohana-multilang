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
	 * @param   string  $uri              URI of the request
	 * @param   array   $client_params    An array of params to pass to the request client
	 * @param   bool    $allow_external   Allow external requests? (deprecated in 3.3)
	 * @param   array   $injected_routes  An array of routes to use, for testing
	 * @return  void|Request
	 * @throws  Request_Exception
	 * @uses    Route::all
	 * @uses    Route::matches
	 * @return  Request
	 */
	public static function factory($uri = TRUE, $client_params = array(), $allow_external = TRUE, $injected_routes = array())
	{
		$config = Kohana::$config->load('multilang');

		// If we don't hide the default language, we must look for a language code for the root uri
		if(Request::detect_uri() === '' AND $config->auto_detect AND $uri === TRUE)
		{
			$lang = Multilang::find_user_language();
			if(!$config->hide_default OR $lang != $config->default)
			{
				// Use the default server protocol
				$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';

				// Redirect to the root URI, but with language prepended
				header($protocol.' 302 Found');
				header('Location: '.URL::base(TRUE, TRUE).$lang.'/');				
				exit;
			}
		}	
		
		return parent::factory($uri, $client_params, $allow_external, $injected_routes);
	}
	
	/**
	 * We don't want to remove the trailing slash from the uri
	 */
	public function __construct($uri, $client_params = array(), $allow_external = TRUE, $injected_routes = array())
	{
		$client_params = is_array($client_params) ? $client_params : array();

		// Initialise the header
		$this->_header = new HTTP_Header(array());

		// Assign injected routes
		$this->_routes = $injected_routes;

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
		// $allow_external = FALSE prevents the default index.php from
		// being able to proxy external pages.
		if ( ! $allow_external OR strpos($uri, '://') === FALSE)
		{
			// Remove trailing slashes from the URI (We don't want that)
			//$this->_uri = trim($uri, '/');
			$this->_uri = ltrim($uri, '/');
			//$this->_route = new Route($uri);
			// Apply the client
			$this->_client = new Request_Client_Internal($client_params);
		}
		else
		{
			// Create a route
			$this->_route = new Route($uri);

			// Store the URI
			$this->_uri = $uri;

			// Set the security setting if required
			if (strpos($uri, 'https://') === 0)
			{
				$this->secure(TRUE);
			}

			// Set external state
			$this->_external = TRUE;

			// Setup the client
			$this->_client = Request_Client_External::factory($client_params);
		}
	}
	
	/**
	 * Altered to detect the language in the uri
	 *
	 * @return  Response
	 * @throws  Request_Exception
	 * @throws  HTTP_Exception_404
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	public function execute()
	{
		if ( ! $this->_external)
		{
			$processed = Request::process($this, $this->_routes);

			if ($processed)
			{
				// Store the matching route
				$this->_route = $processed['route'];
				$params = $processed['params'];

				// Is this route external?
				$this->_external = $this->_route->is_external();

				if (isset($params['directory']))
				{
					// Controllers are in a sub-directory
					$this->_directory = $params['directory'];
				}

				// Store the controller
				$this->_controller = $params['controller'];

				// Store the action
				$this->_action = (isset($params['action']))
					? $params['action']
					: Route::$default_action;

				// These are accessible as public vars and can be overloaded
				unset($params['controller'], $params['action'], $params['directory']);

				// Params cannot be changed once matched
				$this->_params = $params;
			}
		}

		if ( ! $this->_route instanceof Route)
		{
			return HTTP_Exception::factory(404, 'Unable to find a route to match the URI: :uri', array(
				':uri' => $this->_uri,
			))->request($this)
				->get_response();
		}

		if ( ! $this->_client instanceof Request_Client)
		{
			throw new Request_Exception('Unable to execute :uri without a Kohana_Request_Client', array(
				':uri' => $this->_uri,
			));
		}
		
		// Multilang part
		if(Request::$lang === NULL)
		{
			Request::$lang = $this->_route->lang;
		}
		
		$config = Kohana::$config->load('multilang');
		if($config->hide_default AND $this->param('lang') === NULL OR  $this->_route->lang === NULL AND $this->param('lang') === NULL)
		{
			Request::$lang = $config->default;
		}
		else
		{			
			Request::$lang = $this->param('lang');
		}
		Multilang::init();
		
		return $this->_client->execute($this);
	}


}