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

namespace DcGeneral\DataDefinition\Section;

use DcGeneral\DataDefinition\Section\View\ListingConfigInterface;
use DcGeneral\DataDefinition\Section\View\OperationCollectionInterface;
use DcGeneral\DataDefinition\Section\View\PanelLayoutInterface;

/**
 * Interface BasicSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
interface ViewSectionInterface extends ContainerSectionInterface
{
	/**
	 * @return ListingConfigInterface
	 */
	public function getListingConfig();

	/**
	 * @param $location
	 *
	 * @return OperationCollectionInterface
	 */
	public function getOperations($location);

	/**
	 * Retrieve the panel layout.
	 *
	 * @return PanelLayoutInterface
	 */
	public function getPanelLayout();
}
