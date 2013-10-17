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

namespace DcGeneral\View\Widget\Events;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Events\BaseEvent;

class ResolveWidgetErrorMessageEvent
	extends BaseEvent
{
	const NAME = 'DcGeneral\View\Widget\Events\ResolveWidgetErrorMessage';

	/**
	 * @var mixed
	 */
	protected $error;

	public function __construct($error)
	{
		$this->error = $error;
	}

	/**
	 * @param mixed $error
	 */
	public function setError($error)
	{
		$this->error = $error;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getError()
	{
		return $this->error;
	}
}
