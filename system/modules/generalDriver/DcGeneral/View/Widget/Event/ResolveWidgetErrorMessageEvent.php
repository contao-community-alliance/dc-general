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

namespace DcGeneral\View\Widget\Event;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Event\EnvironmentAwareEvent;

class ResolveWidgetErrorMessageEvent
	extends EnvironmentAwareEvent
{
	const NAME = 'DcGeneral\View\Widget\Event\ResolveWidgetErrorMessage';

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
