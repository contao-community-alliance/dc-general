<?php

/**
 * PHP version 5
 * @package    dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

error_reporting(E_ALL);

function includeIfExists($file)
{
	return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
	echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
		'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
		'php composer.phar install'.PHP_EOL;
	exit(1);
}

$loader->add('DcGeneral', __DIR__);

require __DIR__.'/ContaoCommunityAlliance/DcGeneral/Test/TestCase.php';
