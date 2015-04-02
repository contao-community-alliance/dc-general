<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelElementInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * This event is fired when a template instance for a panel element shall be created.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetPanelElementTemplateEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The name of the event.
     */
    const NAME = 'dc-general.view.contao2backend.get.panel.element.template';

    /**
     * The element for which a template shall get retrieved.
     *
     * @var PanelElementInterface
     */
    protected $element;

    /**
     * The template instance.
     *
     * @var ViewTemplateInterface
     */
    protected $template;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface  $environment The environment to attach.
     *
     * @param PanelElementInterface $element     The element for which a template shall get retrieved for.
     */
    public function __construct(EnvironmentInterface $environment, PanelElementInterface $element)
    {
        $this->environment = $environment;
        $this->element     = $element;
    }

    /**
     * Retrieve the panel element.
     *
     * @return PanelElementInterface
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Retrieve the template instance.
     *
     * @return ViewTemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template instance.
     *
     * @param ViewTemplateInterface $template The template instance to store.
     *
     * @return GetPanelElementTemplateEvent
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }
}
