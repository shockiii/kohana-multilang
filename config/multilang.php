<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Multilang module config file
 */

/*
 * List of available languages
 */
return array(
	'default'		=> 'en', // The default language code
	'cookie'		=> 'lang', // The cookie name
	'hide_default'	=> FALSE, // You can hide the language code for the default language
	'auto_detect'	=> TRUE, // Auto detect the user language on the homepage
	/**
	 * The allowed languages
	 * For each language, you need to give a code (2-5 chars) for the key,
	 * the 5 letters i18n language code, the locale and the label for the auto generated language selector menu.
	 */
	/*
	'languages'		=>	array(			
		'en'		=> array(
			'i18n'		=> 'en_US',
			'locale'    => array('en_US.utf-8'),
			'label'		=> 'english',
		),
		'fr'		=>	array(
			'i18n'		=> 'fr_FR',
			'locale'    => array('fr_FR.utf-8'),
			'label'		=> 'français',
		),
		'de'		=>	array(
			'i18n'		=> 'de_DE',
			'locale'    => array('de_DE.utf-8'),
			'label'		=> 'deutsch',
		),			 
	),
	*/
);
