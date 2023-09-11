<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class BuildWidgetEvent.
 *
 * This event is being emitted when the widget manager wants to create a new widget instance.
 */
class BuildWidgetEvent extends AbstractModelAwareEvent
{
    /**
     * The name of the event.
     */
    public const NAME = 'dc-general.view.contao2backend.build-widget';

    /**
     * The property for which a widget shall get instantiated.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * The instantiated widget.
     *
     * @var Widget|null
     */
    protected $widget = null;

    /**
     * Create a new event.
     *
     * @param EnvironmentInterface $environment The environment instance in use.
     * @param ModelInterface       $model       The model holding the data for the widget that shall be instantiated.
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
     * @param Widget $widget The widget instance.
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
     * @return Widget|null
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
