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

namespace DcGeneral\Contao\DataDefinition\Definition;

use DcGeneral\DataDefinition\Definition\View\CommandCollection;
use DcGeneral\DataDefinition\Definition\View\DefaultListingConfig;
use DcGeneral\DataDefinition\Definition\View\DefaultPanelLayout;
use DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
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
	 * @var CommandCollectionInterface
	 */
	protected $globalCommands;

	/**
	 * @var CommandCollectionInterface
	 */
	protected $modelCommands;

	/**
	 * @var PanelLayoutInterface
	 */
	protected $panelLayout;

	public function __construct()
	{
		$this->listingConfig  = new DefaultListingConfig();
		$this->globalCommands = new CommandCollection();
		$this->modelCommands  = new CommandCollection();
		$this->panelLayout    = new DefaultPanelLayout();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListingConfig()
	{
		return $this->listingConfig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGlobalCommands()
	{
		return $this->globalCommands;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModelCommands()
	{
		return $this->modelCommands;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPanelLayout()
	{
		return $this->panelLayout;
	}
}



