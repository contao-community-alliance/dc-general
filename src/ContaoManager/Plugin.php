<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerBundle\ContaoManagerBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoCommunityAlliance\Contao\Bindings\CcaEventsContaoBindingsBundle;
use ContaoCommunityAlliance\DcGeneral\DcGeneralBundle;
use ContaoCommunityAlliance\UrlBuilder\CcaUrlBuilderBundle;
use DependencyInjection\Container\CcaDependencyInjectionBundle;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(DcGeneralBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class,
                        ContaoManagerBundle::class,
                        CcaUrlBuilderBundle::class,
                        CcaDependencyInjectionBundle::class,
                        CcaEventsContaoBindingsBundle::class
                    ]
                ),
        ];
    }
}
