<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ModelFormatterConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class ModelToLabelEvent.
 *
 * This event gets emitted when a model shall be translated to an html representation.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class ModelToLabelEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.model-to-label';

    /**
     * The label for the model.
     *
     * This is a format string to use in vsprintf().
     *
     * @var string
     */
    protected $label;

    /**
     * The label information instance.
     *
     * @var ModelFormatterConfigInterface
     */
    protected $listLabel;

    /**
     * The arguments to use when building the label from the format string.
     *
     * @var array
     */
    protected $args;

    /**
     * Set the arguments to use when generating the final string representation using the format string.
     *
     * @param array $args The arguments.
     *
     * @return ModelToLabelEvent
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Retrieve the arguments to use when generating the final string representation using the format string.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set the label for the model.
     *
     * This is a format string to use in vsprintf().
     *
     * @param string $label The label string.
     *
     * @return ModelToLabelEvent
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label for the model.
     *
     * This is a format string to use in vsprintf().
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label information instance.
     *
     * @param ModelFormatterConfigInterface $listLabel The label information instance.
     *
     * @return ModelToLabelEvent
     */
    public function setFormatter($listLabel)
    {
        $this->listLabel = $listLabel;

        return $this;
    }

    /**
     * Retrieve the label information instance.
     *
     * @return ModelFormatterConfigInterface
     */
    public function getFormatter()
    {
        return $this->listLabel;
    }
}
