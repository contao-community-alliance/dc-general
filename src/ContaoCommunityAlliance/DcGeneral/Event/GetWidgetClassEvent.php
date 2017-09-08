<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Class GetWidgetClassEvent.
 *
 * This event gets emitted when the class name of a widget should be retrieved.
 * This is needed in the `WidgetBuilder`.
 *
 * @see BuildWidgetEvent
 */
class GetWidgetClassEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The model attached to the event.
     * Might not be given on every event dispatch, but when, you can alter the widget based on model data.
     *
     * @var ModelInterface|null
     */
    protected $model;

    /**
     * The property information for which the widget should be created.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * The class name for the widget e.g. `FormTextField`.
     *
     * @var string
     */
    protected $widgetClass;

    /**
     * Create a new event.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param PropertyInterface    $property    The property information for which the widget is created.
     *
     * @param ModelInterface|null  $model       The model for which the widget is created.
     */
    public function __construct(EnvironmentInterface $environment, PropertyInterface $property, $model = null)
    {
        parent::__construct($environment);

        $this->model    = $model;
        $this->property = $property;
    }

    /**
     * Retrieve the attached model.
     *
     * @return ModelInterface|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Retrieve the property information for which the widget is created for.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Retrieve the class name of the widget.
     *
     * @return string
     */
    public function getWidgetClass()
    {
        return $this->widgetClass;
    }

    /**
     * Set the class name of the widget.
     *
     * @param string $widgetClass
     *
     * @return self
     */
    public function setWidgetClass($widgetClass)
    {
        $this->widgetClass = $widgetClass;

        return $this;
    }
}
