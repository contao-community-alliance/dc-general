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

use DcGeneral\DataDefinition\Interfaces\Container;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\Interfaces\Driver;
use DcGeneral\Data\Interfaces\Model;
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
	 * @return Driver
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
	 * @return Container
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
	 * @param Model $objCurrentModel
	 */
	public function setCurrentModel(Model $objCurrentModel);

	/**
	 *
	 * @return Model
	 */
	public function getCurrentModel();
}

