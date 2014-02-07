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

/**
 * This event is emitted after a model has been duplicated.
 *
 * @package DcGeneral\Event
 */
class PostDuplicateModelEvent extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.model.post-duplicate';

	/**
	 * The source model.
	 *
	 * @var ModelInterface
	 */
	protected $sourceModel;

	/**
	 * Create a new instance.
	 *
	 * @param EnvironmentInterface $environment The environment.
	 *
	 * @param ModelInterface       $model       The new model.
	 *
	 * @param ModelInterface       $sourceModel The source model.
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model, ModelInterface $sourceModel)
	{
		parent::__construct($environment, $model);
		$this->sourceModel = $sourceModel;
	}

	/**
	 * Retrieve the source model.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getSourceModel()
	{
		return $this->sourceModel;
	}
}
