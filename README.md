# Kohana Multilang Module

Multilingual module for Kohana PHP Framework, version 3.1

Partly based on this module https://github.com/GeertDD/kohana-lang

## Features

* Language segment in uri
* Works with normal routes
* Custom routes for each language (localization or parameters)
* Auto language detection or cookie
* Language selection menu

## Usage

### Configuration

	return array(
		'default'		=> 'en', // The default language code
		'cookie'		=> 'lang', // The cookie name
		/**
		 * The allowed languages
		 * For each language, you need to give a code (2-5 chars) for the key,
		 * the 5 letters i18n language code, the locale and the label for the auto generated language selector menu.
		 */
		'languages'		=>	array( 
			/*
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
			 */
		),
	);

### 
