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

namespace DcGeneral\Contao\Dca;

use DcGeneral\DataDefinition\ListLabelInterface;

class ListLabel
	implements ListLabelInterface
{
	/**
	* The ContainerInterface instance to which this condition belongs to.
	*
	* @var \DcGeneral\Contao\Dca\Container
	*/
	protected $objParent;

	public function __construct($objParent)
	{
		$this->objParent = $objParent;
	}

	/**
	 * Retrieve some arbitrary data for the label.
	 *
	 * @param string $strKey The key to retrieve from the data container information.
	 *
	 * @return mixed
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('list/label/%s', $strKey));
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFields()
	{
		return $this->get('fields');
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFormat()
	{
		return $this->get('format');
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getMaxCharacters()
	{
		return $this->get('maxCharacters');
	}

	public function isShowColumnsActive()
	{
		return $this->get('showColumns');
	}
}
