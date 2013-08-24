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

namespace DcGeneral\Contao\Dca\Conditions;

use DcGeneral\DataDefinition\ParentChildConditionInterface;
use DcGeneral\DataDefinition\ContainerInterface;

/**
 * Class ParentChildCondition
 *
 * array(
'from'                => 'self',
'to'                  => 'self',
'setOn'               => array
(
array(
'to_field'    => 'pid',
'from_field'  => 'id',
),
array(
'to_field'    => 'fid',
'from_field'  => 'fid',
),
),
'filter'              => array
(
array
(
'local'       => 'pid',
'remote'      => 'id',
'operation'   => '=',
),
)
)
 *
 *
 *
 * @package DcGeneral\Contao\Dca\Conditions
 */
class ParentChildCondition implements ParentChildConditionInterface
{
	/**
	 * The ContainerInterface instance to which this condition belongs to.
	 *
	 * @var \DcGeneral\Contao\Dca\Container
	 */
	protected $objParent;

	/**
	 * The key of this condition in the DCA.
	 *
	 * @var string
	 */
	protected $intKey;

	public function __construct($objParent, $intKey)
	{
		$this->objParent = $objParent;
		$this->intKey    = $intKey;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilter($objParent)
	{
		return $this->get('filter');
	}

	/**
	 * {@inheritedDoc}
	 */
	public function applyTo($objParent, $objChild)
	{

	}

	/**
	 * {@inheritedDoc}
	 */
	public function matches($objParent, $objChild)
	{

	}

	/**
	 * {@inheritedDoc}
	 */
	public function getSourceName()
	{
		return $this->get('from');
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getDestinationName()
	{
		return $this->get('to');
	}

	/**
	 * Retrieve some arbitrary data for the condition.
	 *
	 * @param string $strKey The key to retrieve from the data container information.
	 *
	 * @return mixed
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('dca_config/childCondition/%s/%s', $this->intKey, $strKey));
	}
}




