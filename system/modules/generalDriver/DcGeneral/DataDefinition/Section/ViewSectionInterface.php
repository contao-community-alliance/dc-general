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

/**
 * Interface BasicSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
interface ViewSectionInterface extends ContainerSectionInterface
{
	/**
	 * The name of the section.
	 */
	const NAME = null;

	/**
	 * @return ListingConfigInterface
	 */
	public function getListingConfig();

	/**
	 * @return OperationCollectionInterface
	 */
	public function getOperations($location);

}
