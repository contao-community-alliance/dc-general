<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\ViewAwareInterface;

/**
 * Abstract event class referencing an environment and a view.
 *
 * @package DcGeneral\Event
 */
class AbstractViewAwareEvent extends AbstractEnvironmentAwareEvent implements ViewAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        return $this->getEnvironment()->getView();
    }
}
