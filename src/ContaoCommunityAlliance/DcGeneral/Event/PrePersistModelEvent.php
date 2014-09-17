<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event is emitted just before a model is saved to the data provider.
 *
 * @package DcGeneral\Event
 */
class PrePersistModelEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.model.pre-persist';

    /**
     * The original model attached to the event.
     *
     * @var ModelInterface|null
     */
    protected $originalModel;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface $environment   The environment.
     *
     * @param ModelInterface       $model         The model attached to the event.
     *
     * @param ModelInterface|null  $originalModel The original state of the model (persistent in the data provider).
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        ModelInterface $originalModel = null
    ) {
        parent::__construct($environment, $model);

        $this->originalModel = $originalModel;
    }

    /**
     * Return the original state of the model.
     *
     * May be null on create.
     *
     * @return ModelInterface|null
     */
    public function getOriginalModel()
    {
        return $this->originalModel;
    }
}
