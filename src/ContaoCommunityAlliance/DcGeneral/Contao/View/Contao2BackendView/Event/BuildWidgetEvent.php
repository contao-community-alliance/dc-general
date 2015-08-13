<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class BuildWidgetEvent.
 *
 * This event is being emitted when the widget manager wants to create a new widget instance.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class BuildWidgetEvent extends AbstractModelAwareEvent
{
    /**
     * The name of the event.
     */
    const NAME = 'dc-general.view.contao2backend.build-widget';

    /**
     * The property for which a widget shall get instantiated.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * The instantiated widget.
     *
     * @var \Widget
     */
    protected $widget;

    /**
     * Create a new event.
     *
     * @param EnvironmentInterface $environment The environment instance in use.
     *
     * @param ModelInterface       $model       The model holding the data for the widget that shall be instantiated.
     *
     * @param PropertyInterface    $property    The property for which the widget shall be instantiated.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyInterface $property
    ) {
        parent::__construct($environment, $model);

        $this->property = $property;
    }

    /**
     * Stores the widget instance into the event.
     *
     * @param \Widget $widget The widget instance.
     *
     * @return BuildWidgetEvent
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * Retrieve the widget instance from the event.
     *
     * @return \Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Retrieve the property definition from the event for which the widget shall be instantiated.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }
}
