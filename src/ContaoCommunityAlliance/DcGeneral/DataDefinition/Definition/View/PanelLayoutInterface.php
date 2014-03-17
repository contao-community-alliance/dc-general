<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * This interface describes a panel layout definition.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
interface PanelLayoutInterface
{
	/**
	 * Return rows of panel elements.
	 *
	 * @return PanelRowCollectionInterface
	 */
	public function getRows();
}
