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

namespace DcGeneral\Contao\Callback;

use DcGeneral\Exception\DcGeneralRuntimeException;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractStaticCallbackListener extends AbstractCallbackListener
{
	/**
	 * @var array
	 */
	protected $args;

	function __construct($callback, $_ = null)
	{
		parent::__construct($callback);

		$args = func_get_args();
		array_shift($args);

		$this->args = $args;
	}

	/**
	 * @return array
	 */
	public function getArgs(Event $event = null)
	{
		return $this->args;
	}
}
