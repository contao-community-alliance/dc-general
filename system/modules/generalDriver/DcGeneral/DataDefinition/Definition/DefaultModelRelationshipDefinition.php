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

class DefaultModelRelationshipDefinition implements ModelRelationshipDefinitionInterface
{
	protected $rootCondition;

	/**
	 * @var \DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface[]
	 */
	protected $childConditions = array();

	/**
	 * {@inheritdoc}
	 */
	public function setRootCondition($condition)
	{
		$this->rootCondition = $condition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootCondition()
	{
		return $this->rootCondition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addChildCondition($condition)
	{
		$hash = spl_object_hash($condition);
		$this->childConditions[$hash] = $condition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChildCondition($srcProvider, $dstProvider)
	{
		foreach ($this->getChildConditions($srcProvider) as $condition)
		{
			if ($condition->getDestinationName() == $dstProvider)
			{
				return $condition;
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChildConditions($srcProvider = '')
	{

		if (!$this->childConditions)
		{
			return array();
		}

		$arrReturn = array();
		foreach ($this->childConditions as $condition)
		{
			if ($condition->getSourceName() != $srcProvider)
			{
				continue;
			}

			$arrReturn[] = $condition;
		}

		return $arrReturn;
	}
}
