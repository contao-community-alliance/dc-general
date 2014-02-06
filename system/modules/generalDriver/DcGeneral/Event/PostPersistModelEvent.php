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
 * This event is emitted after a model has been saved to the data provider.
 *
 * @package DcGeneral\Event
 */
class PostPersistModelEvent extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.model.post-persist';

	/**
	 * The original model attached to the event.
	 *
	 * @var ModelInterface
	 */
	protected $originalModel;

	/**
	 * Create a new model aware event.
	 *
	 * @param EnvironmentInterface $environment   The environment.
	 *
	 * @param ModelInterface       $model         The model attached to the event.
	 *
	 * @param ModelInterface       $originalModel The original state of the model (persistent in the data provider).
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model, ModelInterface $originalModel)
	{
		parent::__construct($environment, $model);

		$this->originalModel = $originalModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOriginalModel()
	{
		return $this->originalModel;
	}
}
