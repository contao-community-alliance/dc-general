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

namespace DcGeneral\Contao\View\Contao2BackendView\Event;

use DcGeneral\Event\AbstractModelAwareEvent;

class ParentViewChildRecordEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.parent-view-child-record';

	/**
	 * @var string
	 */
	protected $html;

	/**
	 * Set the html code to use as child record.
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	public function setHtml($html)
	{
		$this->html = $html;

		return $this;
	}

	/**
	 * Retrieve the stored html code for the child record.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}
}
