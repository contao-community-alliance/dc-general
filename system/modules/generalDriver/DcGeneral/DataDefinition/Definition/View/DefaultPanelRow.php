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

namespace DcGeneral\DataDefinition\Definition\View;

use DcGeneral\DataDefinition\Definition\View\Panel\ElementInformationInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class DefaultPanelRow implements PanelRowInterface
{
	/**
	 * @var ElementInformationInterface[]
	 */
	protected $elements = array();

	/**
	 * {@inheritDoc}
	 */
	public function getElements()
	{
		$names = array();
		foreach ($this as $element)
		{
			/** @var ElementInformationInterface $element */
			$names[] = $element->getName();
		}

		return $names;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addElement(ElementInformationInterface $element, $index = -1)
	{
		if ($this->hasElement($element))
		{
			return $this;
		}

		if (($index < 0) || ($this->getCount() <= $index))
		{
			$this->elements[] = $element;
		}
		else
		{
			array_splice($this->elements, $index, 0, $element);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteElement($indexOrNameOrInstance)
	{
		if ($indexOrNameOrInstance instanceof ElementInformationInterface)
		{
			array_filter($this->elements, function($element) use ($indexOrNameOrInstance) {
				/** @var ElementInformationInterface $element */

				return $element == $indexOrNameOrInstance;
			});
		}
		elseif (is_string($indexOrNameOrInstance))
		{
			foreach ($this as $index => $element)
			{
				/** @var ElementInformationInterface $element */
				if ($indexOrNameOrInstance == $element->getName())
				{
					unset($this->elements[$index]);
					break;
				};
			}
		}
		elseif (is_numeric($indexOrNameOrInstance))
		{
			unset($this->elements[$indexOrNameOrInstance]);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasElement($instanceOrName)
	{
		if ($instanceOrName instanceof ElementInformationInterface)
		{
			return in_array($instanceOrName, $this->elements);
		}

		if (is_string($instanceOrName))
		{
			foreach ($this as $element)
			{
				/** @var ElementInformationInterface $element */
				if ($instanceOrName == $element->getName())
				{
					return true;
				};
			}

			return false;
		}

		throw new DcGeneralInvalidArgumentException('Invalid value for element name given.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCount()
	{
		return count($this->elements);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getElement($indexOrName)
	{
		if (is_string($indexOrName))
		{
			foreach ($this as $element)
			{
				/** @var ElementInformationInterface $element */
				if ($indexOrName == $element->getName())
				{
					return $element;
				};
			}
		}
		elseif (!is_numeric($indexOrName))
		{
			throw new DcGeneralInvalidArgumentException('Invalid value for element name given.');
		}

		if (!isset($this->elements[$indexOrName]))
		{
			throw new DcGeneralInvalidArgumentException('Value out of bounds: ' . $indexOrName . '.');
		}

		return $this->elements[$indexOrName];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->elements);
	}
}
