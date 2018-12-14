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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Default implementation of a panel layout definition.
 */
class DefaultPanelLayout implements PanelLayoutInterface
{
    /**
     * The rows of the layout.
     *
     * @var PanelRowCollectionInterface
     */
    protected $rows;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->rows = new DefaultPanelRowCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getRows()
    {
        return $this->rows;
    }
}
