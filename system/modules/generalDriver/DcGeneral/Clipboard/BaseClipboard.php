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

namespace DcGeneral\Clipboard;

use DcGeneral\Clipboard\Interfaces\Clipboard;

class BaseClipboard implements Clipboard
{
	/**
	 * The ids contained.
	 *
	 * @var array
	 */
	protected $arrIds;

	/**
	 * The ids that will create a circular reference and therefore shall get ignored for pasting.
	 *
	 * @var array
	 */
	protected $arrCircularIds;

	/**
	 * @var string
	 */
	protected $mode;

	/**
	 * {@inheritDoc}
	 */
	public function loadFrom($objEnvironment)
	{
		$strName      = $objEnvironment->getDataDefinition()->getName();
		$arrClipboard = $objEnvironment->getInputProvider()->getPersistentValue('CLIPBOARD');

		if (isset($arrClipboard[$strName]))
		{
			if (isset($arrClipboard[$strName]['ignoredIDs']))
			{
				$this->setCircularIds($arrClipboard[$strName]['ignoredIDs']);
			}

			if (isset($arrClipboard[$strName]['ids']))
			{
				$this->setContainedIds($arrClipboard[$strName]['ids']);
			}

			if (isset($arrClipboard[$strName]['mode']))
			{
				$this->mode = $arrClipboard[$strName]['mode'];
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function saveTo($objEnvironment)
	{
		$strName      = $objEnvironment->getDataDefinition()->getName();
		$arrClipboard = $objEnvironment->getInputProvider()->getPersistentValue('CLIPBOARD');

		if ($this->isEmpty())
		{
			unset($arrClipboard[$strName]);
		}
		else
		{
			$arrClipboard[$strName] = array();
			if ($this->getCircularIds())
			{
				$arrClipboard[$strName]['ignoredIDs'] = $this->getCircularIds();
			}

			if ($this->isNotEmpty())
			{
				$arrClipboard[$strName]['ids'] = $this->getContainedIds();
			}

			$arrClipboard[$strName]['ids']['mode'] = $this->mode;
		}
	}

	public function clear()
	{

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isEmpty()
	{
		return (!isset($this->arrIds)) || empty($this->arrIds);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isNotEmpty()
	{
		return !$this->isEmpty();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isCut()
	{
		return $this->mode == 'cut';
	}

	/**
	 * {@inheritDoc}
	 */
	public function isCopy()
	{
		return $this->mode == 'copy';
	}

	/**
	 * {@inheritDoc}
	 */
	public function copy($ids)
	{
		$this->mode = 'copy';

		if (is_array($ids) || is_null($ids))
		{
			$this->setContainedIds($ids);
		}
		else
		{
			$this->setContainedIds(array($ids));
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cut($ids)
	{
		$this->mode = 'cut';

		if (is_array($ids) || is_null($ids))
		{
			$this->setContainedIds($ids);
		}
		else
		{
			$this->setContainedIds(array($ids));
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainedIds($arrIds)
	{
		$this->arrIds = $arrIds;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getContainedIds()
	{
		return $this->arrIds;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCircularIds($arrIds)
	{
		$this->arrCircularIds = $arrIds;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCircularIds()
	{
		return $this->arrCircularIds;
	}
}
