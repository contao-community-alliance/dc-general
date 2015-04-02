<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Default implementation of a panel layout definition.
 *
 * @package DcGeneral\DataDefinition\Definition\View
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
