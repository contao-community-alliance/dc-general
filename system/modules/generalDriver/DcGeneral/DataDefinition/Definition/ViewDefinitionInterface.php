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

namespace DcGeneral\DataDefinition\Definition;

use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use DcGeneral\DataDefinition\Definition\View\PanelLayoutInterface;

/**
 * Interface BasicDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface ViewDefinitionInterface extends DefinitionInterface
{
	/**
	 * @return ListingConfigInterface
	 */
	public function getListingConfig();

	/**
	 * @param $location
	 *
	 * @return CommandCollectionInterface
	 */
	public function getOperations($location);

	/**
	 * Retrieve the panel layout.
	 *
	 * @return PanelLayoutInterface
	 */
	public function getPanelLayout();
}
