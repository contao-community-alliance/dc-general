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

namespace DcGeneral\Event;

use DcGeneral\Data\ModelInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\ModelAwareInterface;

class PostDuplicateModelEvent extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.model.post-duplicate';

	/**
	 * @var ModelInterface
	 */
	protected $sourceModel;

	public function __construct(EnvironmentInterface $environment, ModelInterface $model, ModelInterface $sourceModel)
	{
		parent::__construct($environment, $model);
		$this->sourceModel = $sourceModel;
	}

	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getSourceModel()
	{
		return $this->sourceModel;
	}
}
