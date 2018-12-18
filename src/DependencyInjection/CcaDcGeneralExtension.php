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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class CcaDcGeneralExtension extends Extension
{
    /**
     * The configuration files.
     *
     * @var string[]
     */
    private $files = [
        'contao/backend_event_listeners.yml',
        'contao/button_backend_listeners.yml',
        'contao/event_common_subscribers.yml',
        'contao/handler_backend_listeners.yml',
        'contao/handler_multiple_backend_listeners.yml',
        'contao/legacy_backend_listeners.yml',
        'contao/legacy_common_listeners.yml',
        'contao/picker_provider.yml',
        'contao/populater_backend_listeners.yml',
        'contao/populater_common_listeners.yml',
        'contao/widget_backend_listeners.yml',
        'backend_event_subscribers.yml',
        'config.yml',
        'event_listeners.yml',
        'services.yml'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach ($this->files as $file) {
            $loader->load($file);
        }
    }
}
