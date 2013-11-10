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

use DcGeneral\DataDefinition\Definition\View\DefaultListingConfig;
use DcGeneral\DataDefinition\Definition\View\DefaultPanelLayout;
use DcGeneral\DataDefinition\Definition\View\OperationCollectionInterface;
use DcGeneral\DataDefinition\Definition\View\PanelLayoutInterface;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;

/**
 * Interface BasicDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class Contao2BackendViewDefinition implements Contao2BackendViewDefinitionInterface
{
	/**
	 * @var ListingConfigInterface
	 */
	protected $listingConfig;

	/**
	 * @var OperationCollectionInterface
	 */
	protected $operations;

	/**
	 * @var PanelLayoutInterface
	 */
	protected $panelLayout;

	public function __construct()
	{
		$this->listingConfig = new DefaultListingConfig();
		// $this->operations    = new DefaultBackendOperationCollection;

		$this->panelLayout   = new DefaultPanelLayout();
	}

	/**
	 * @return ListingConfigInterface
	 */
	public function getListingConfig()
	{
		return $this->listingConfig;
	}

	/**
	 * @param $location
	 *
	 * @return OperationCollectionInterface
	 */
	public function getOperations($location)
	{
		return $this->operations;
	}

	/**
	 * Retrieve the panel layout.
	 *
	 * @return PanelLayoutInterface
	 */
	public function getPanelLayout()
	{
		return $this->panelLayout;
	}
}



