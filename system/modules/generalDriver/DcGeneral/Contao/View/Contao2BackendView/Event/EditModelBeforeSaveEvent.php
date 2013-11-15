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

use DcGeneral\Data\ModelInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\AbstractEnvironmentAwareEvent;

class EditModelBeforeSaveEvent
	extends AbstractEnvironmentAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.edit.before-save-model';

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * Create a new environment aware event.
	 *
	 * @param EnvironmentInterface           $environment
	 *
	 * @param \DcGeneral\Data\ModelInterface $model
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model)
	{
		parent::__construct($environment);
		$this->model = $model;
	}

	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}
}

