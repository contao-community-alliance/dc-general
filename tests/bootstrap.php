<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

\error_reporting(E_ALL);

function includeIfExists($file)
{
    return \file_exists($file) ? include $file : false;
}

if (
    // Locally installed dependencies
    (!$loader = \includeIfExists(__DIR__.'/../vendor/autoload.php'))
    // We are within an composer install.
    && (!$loader = \includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

// We need the Contao interfaces - sadly they are excluded from classmap. :/
$reflection = new \ReflectionClass(\Contao\CoreBundle\ContaoCoreBundle::class);

require_once \dirname($reflection->getFileName()) . '/Resources/contao/helper/interface.php';
