<?php
/**
 * The default route
 * It's a bit tricky and particular since it got no translations.
 * We need to create a general route for this one
 *
 * It is recommended to move this route into your bootstrap and adapt it.
 */

/*
$languages = array();
$lang_param = '<lang>/';

$config = Kohana::$config->load('multilang');

// Need a regex for all the available languages
foreach($config->languages as $lang => $settings)
{
	// If we hdie the default language, we make lang parameter optional
	if($config->.hide_default && $config->default === $lang)
	{
		$lang_param = '(<lang>/)';
	}
	else
	{
		$languages[] = $lang;
	}
}

Route::set('default', $lang_param, array(
	'lang'	=> '('.  implode('|', $languages).')',
))->defaults(array(
	'controller'	=> 'home',
	'action'		=> 'index',
	'lang'			=> $config->default,
));
 */