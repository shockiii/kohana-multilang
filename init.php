<?php
/**
 * The default route
 * It's a bit tricky and particular since it got no translations.
 * We need to create a general route for this one
 */

$languages = array();
$lang_param = '<lang>/';

// Need a regex for all the available languages
foreach(Kohana::config('multilang.languages') as $lang => $settings)
{
	// If we hdie the default language, we make lang parameter optional
	if(Kohana::config('multilang.hide_default') && Kohana::config('multilang.default') === $lang)
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
	'lang'			=> NULL,
));