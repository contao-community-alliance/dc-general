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

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\DcGeneral\ViewAwareInterface;

/**
 * Abstract event class referencing an environment and a view.
 */
class AbstractViewAwareEvent extends AbstractEnvironmentAwareEvent implements ViewAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        $view = $this->getEnvironment()->getView();
        assert($view instanceof ViewInterface);

        return $view;
    }
}
