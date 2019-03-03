<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use PhpCodeQuality\AutoloadValidation\ClassLoader\EnumeratingClassLoader;
use PhpCodeQuality\AutoloadValidation\Exception\ParentClassNotFoundException;

// This is the hack to mimic the Contao auto loader.
spl_autoload_register(
    function ($class) {
        if (in_array($class, ['listable', 'editable', 'executable', 'uploadable'])) {
            $reflection = new ReflectionClass(\Contao\CoreBundle\ContaoCoreBundle::class);
            require_once dirname($reflection->getFileName()) . '/Resources/contao/helper/interface.php';
            return true;
        }
        if (substr($class, 0, 7) === 'Contao\\') {
            return null;
        }
        try {
            spl_autoload_call('Contao\\' . $class);
        } catch (ParentClassNotFoundException $exception) {
            return null;
        }
        if (EnumeratingClassLoader::isLoaded('Contao\\' . $class) && !EnumeratingClassLoader::isLoaded($class)) {
            class_alias('Contao\\' . $class, $class);
            return true;
        }

        return null;
    }
);
