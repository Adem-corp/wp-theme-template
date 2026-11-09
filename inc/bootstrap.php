<?php
/**
 * Load independent theme modules.
 *
 * @package Theme_name
 */

$adem_modules = array(
	'theme/setup.php',
	'theme/assets.php',
	'theme/filters.php',
	'theme/helpers.php',
	'forms/registration.php',
	'forms/spam.php',
	'forms/traffic.php',
	'forms/submission.php',
	'integrations/acf.php',
	'integrations/rest.php',
	'integrations/svg.php',
	'integrations/tiny-mce.php',
);

foreach ( $adem_modules as $adem_module ) {
	require_once __DIR__ . '/' . $adem_module;
}

unset( $adem_modules, $adem_module );
