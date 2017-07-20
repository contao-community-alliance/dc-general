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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;

/**
 * Class ContainerOnCopyCallbackListener.
 *
 * Handle callbacks to be invoked when a copy operation is made.
 */
class ContainerOnCopyCallbackListener extends AbstractCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param PostDuplicateModelEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array($event->getModel()->getId(), new DcCompat($event->getEnvironment(), $event->getSourceModel()));
    }
}
