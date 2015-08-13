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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/** @var Pimple $container */

/**
 * This function creates the default data definition container.
 * To override the type, set your own implementation override the value of
 * $container['dc-general.data-definition-container.factory'].
 *
 * @return \ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface $container
 */
$container['dc-general.data-definition-container.factory.default'] = $container->protect(
    function () {
        return new \ContaoCommunityAlliance\DcGeneral\DataDefinitionContainer();
    }
);

if (!isset($container['dc-general.data-definition-container.factory'])) {
    $container['dc-general.data-definition-container.factory'] =
        $container->raw('dc-general.data-definition-container.factory.default');
}

$container['dc-general.data-definition-container'] = $container->share(
    function ($container) {
        $factory = $container['dc-general.data-definition-container.factory'];

        /** @var \ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface $container */
        $container = $factory();

        return $container;
    }
);
