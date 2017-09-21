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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener;

/**
 * Class ParentView.
 *
 * Implementation of the parent view.
 */
class ParentView extends BaseView
{
    /**
     * {@inheritDoc}
     *
     * @deprecated Use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener
     *
     * @see \ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener
     */
    public function enforceModelRelationship($model)
    {
        // Fallback implementation.
        $listener = new ParentEnforcingListener();
        $listener->process(new EnforceModelRelationshipEvent($this->getEnvironment(), $model));
    }

}
