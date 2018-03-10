<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
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
 * Class ManipulateWidgetEvent.
 *
 * This event gets emitted when a widget shall get manipulated.
 * This happens directly after all the initialization has been done.
 *
 * If you want to create the widget entirely by yourself refer to the BuildWidgetEvent event.
 *
 * @see BuildWidgetEvent
 */
class ManipulateWidgetEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.manipulate-widget';

    /**
     * The widget instance to manipulate.
     *
     * @var Widget
     */
    protected $widget;

    /**
     * The property information for which the widget has been created.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * Create a new event.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param ModelInterface       $model       The model for which the widget is created.
     * @param PropertyInterface    $property    The property information for which the widget is created.
     * @param Widget               $widget      The widget instance to manipulate.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyInterface $property,
        Widget $widget
    ) {
        parent::__construct($environment, $model);

        $this->property = $property;
        $this->widget   = $widget;
    }

    /**
     * Retrieve the widget instance.
     *
     * @return \Widget
     */
    public function getWidget()
    {
        return $this->widget;
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
}
