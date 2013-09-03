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

namespace DcGeneral\Contao\Dca\Conditions;

use DcGeneral\DataDefinition\RootConditionInterface;
use DcGeneral\DataDefinition\AbstractCondition;
use DcGeneral\Data\ModelInterface;

class RootCondition
	extends AbstractCondition
	implements RootConditionInterface
{
	/**
	 * The Container instance to which this condition belongs to.
	 *
	 * @var \DcGeneral\Contao\Dca\Container
	 */
	protected $objParent;

	/**
	 * The name of the table this condition is being applied to.
	 *
	 * @var string
	 */
	protected $strTable;

	public function __construct($objParent, $strTable)
	{
		$this->objParent = $objParent;
		$this->strTable  = $strTable;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilter()
	{
		return $this->get('filter');
	}

	/**
	 * Apply a condition to a model.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return void
	 */
	public function applyTo($objModel)
	{
		$arrCondition = $this->get('setOn');
		if ($arrCondition)
		{

		}
	}

	/**
	 * Test if the given model is indeed a root object for this condition.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return bool
	 */
	public function matches($objModel)
	{
		$arrCondition = $this->get('filter');
		if ($arrCondition)
		{

		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('dca_config/rootEntries/%s/%s', $this->strTable, $strKey));
	}
}
