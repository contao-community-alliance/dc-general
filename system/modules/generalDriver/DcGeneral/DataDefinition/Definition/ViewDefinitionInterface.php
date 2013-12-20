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
 * This interface describes the base information about views.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface ViewDefinitionInterface extends DefinitionInterface
{
	/**
	 * Retrieve the listing configuration.
	 *
	 * @return ListingConfigInterface
	 */
	public function getListingConfig();

	/**
	 * Retrieve the global commands.
	 *
	 * @return CommandCollectionInterface
	 */
	public function getGlobalCommands();

	/**
	 * Retrieve the model command colletion.
	 *
	 * @return CommandCollectionInterface
	 */
	public function getModelCommands();

	/**
	 * Retrieve the panel layout.
	 *
	 * @return PanelLayoutInterface
	 */
	public function getPanelLayout();
}
