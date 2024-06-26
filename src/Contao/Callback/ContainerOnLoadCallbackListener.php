<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;

/**
 * Class ContainerOnLoadCallbackListener.
 *
 * Handle onload_callbacks.
 *
 * @extends AbstractCallbackListener<CreateDcGeneralEvent>
 */
class ContainerOnLoadCallbackListener extends AbstractCallbackListener
{
    /**
     * {@inheritDoc}
     */
    public function wantToExecute($event)
    {
        if (null === $this->dataContainerName) {
            return true;
        }
        if (null === $definition = $event->getDcGeneral()->getEnvironment()->getDataDefinition()) {
            throw new \LogicException('No data definition given.');
        }
        return ($this->dataContainerName === $definition->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        return [new DcCompat($event->getDcGeneral()->getEnvironment())];
    }
}
