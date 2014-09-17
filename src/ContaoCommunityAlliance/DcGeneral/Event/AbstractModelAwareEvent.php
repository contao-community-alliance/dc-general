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
use ContaoCommunityAlliance\DcGeneral\ModelAwareInterface;

/**
 * Abstract base class for an event that need an environment and a model.
 *
 * @package DcGeneral\Event
 */
class AbstractModelAwareEvent extends AbstractEnvironmentAwareEvent implements ModelAwareInterface
{

    /**
     * The model attached to the event.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The model attached to the event.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        parent::__construct($environment);
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }
}
