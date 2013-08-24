<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Interfaces;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DriverInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Panel\Interfaces\Container as PanelContainer;

interface DataContainer extends \editable, \listable
{
	/**
	 * Return the name of the root data provider.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the Environment for the DC.
	 *
	 * @return Environment
	 */
	public function getEnvironment();

	/**
	 * Return default data provider if no source is given. Else search for config
	 * for given param or return default data provider for given source.
	 *
	 * @param string $strSource
	 *
	 * @return DriverInterface
	 */
	public function getDataProvider($strSource = null);

	/**
	 * Retrieve the Input provider.
	 *
	 * @return InputProvider
	 */
	public function getInputProvider();

	/**
	 * Retrieve the data container definition.
	 *
	 * @return ContainerInterface
	 */
	public function getDataDefinition();

	/**
	 *
	 * @param CollectionInterface $objCurrentCollection
	 *
	 * @return void
	 */
	public function setCurrentCollection(CollectionInterface $objCurrentCollection);

	/**
	 *
	 * @return CollectionInterface
	 */
	public function getCurrentCollection();

	/**
	 *
	 * @param ModelInterface $objCurrentModel
	 */
	public function setCurrentModel(ModelInterface $objCurrentModel);

	/**
	 *
	 * @return ModelInterface
	 */
	public function getCurrentModel();
}

