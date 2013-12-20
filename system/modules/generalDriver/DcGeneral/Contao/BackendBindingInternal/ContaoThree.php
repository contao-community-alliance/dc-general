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

namespace DcGeneral\Contao\BackendBindingInternal;

/**
 * Class ContaoThree.
 *
 * Contao 3 bindings.
 *
 * @package DcGeneral\Contao\BackendBindingInternal
 */
class ContaoThree extends \Backend
{
	/**
	 * The singleton instance.
	 *
	 * @var ContaoThree
	 */
	protected static $objInstance;

	/**
	 * Create a new instance. This is not directly creatable, use the getInstance() method to retrieve the singleton.
	 */
	protected function __construct()
	{
		self::$objInstance = $this;
		parent::__construct();
	}

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return ContaoThree
	 */
	public static function getInstance()
	{
		if (!self::$objInstance)
		{
			new self;
		}

		return self::$objInstance;
	}

	/**
	 * Load a set of DCA files.
	 *
	 * @param string  $strName    The table name.
	 *
	 * @param boolean $blnNoCache If true, the cache will be bypassed.
	 *
	 * @return void
	 */
	public function loadDataContainer($strName, $blnNoCache=false)
	{
		parent::loadDataContainer($strName, $blnNoCache);
	}
}

