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

namespace ContaoCommunityAlliance\DcGeneral\View\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * This event is emitted when a property value of a model shall be transformed into a readable string representation.
 *
 * @package DcGeneral\View\Event
 */
class RenderReadablePropertyValueEvent
    extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.render-readable-property-value';

    /**
     * The property that shall be transformed.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * The value that shall be transformed.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The transformed string representation.
     *
     * @var string|null
     */
    protected $rendered = null;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param ModelInterface       $model       The model the value originates from.
     *
     * @param PropertyInterface    $property    The property to transform.
     *
     * @param mixed                $value       The value to transform.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyInterface $property,
        $value
    )
    {
        parent::__construct($environment, $model);
        $this->property = $property;
        $this->value    = $value;
    }

    /**
     * Retrieve the property.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Retrieve the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the rendered value.
     *
     * @param null|string $rendered The rendered string representation or null to clear the representation.
     *
     * @return RenderReadablePropertyValueEvent
     */
    public function setRendered($rendered)
    {
        $this->rendered = $rendered;
        return $this;
    }

    /**
     * Retrieve the rendered string representation.
     *
     * @return null|string
     */
    public function getRendered()
    {
        return $this->rendered;
    }
}
