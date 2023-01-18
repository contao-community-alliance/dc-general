<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;

/**
 * Class ContainerPasteRootButtonCallbackListener.
 *
 * Handler for the paste into root buttons.
 *
 * @extends AbstractReturningCallbackListener<GetPasteRootButtonEvent>
 */
class ContainerPasteRootButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        if (null === $provider = $event->getEnvironment()->getDataProvider()) {
            throw new \LogicException('No data provider given.');
        }
        if (null === $definition = $event->getEnvironment()->getDataDefinition()) {
            throw new \LogicException('No data definition given.');
        }

        return [
            new DcCompat($event->getEnvironment()),
            $provider->getEmptyModel()->getPropertiesAsArray(),
            $definition->getName(),
            false,
            [],
            null,
            null
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        if (null === $value) {
            return;
        }

        $event->setHtml($value);
        $event->stopPropagation();
    }
}
