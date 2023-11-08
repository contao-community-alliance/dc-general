<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ModelFormatterConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class ModelToLabelEvent.
 *
 * This event gets emitted when a model shall be translated to an html representation.
 */
class ModelToLabelEvent extends AbstractModelAwareEvent
{
    public const NAME = 'dc-general.view.contao2backend.model-to-label';

    /**
     * The label for the model.
     *
     * This is a format string to use in vsprintf().
     *
     * @var string
     */
    protected $label = '';

    /**
     * The label information instance.
     *
     * @var ModelFormatterConfigInterface
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $listLabel;

    /**
     * The arguments to use when building the label from the format string.
     *
     * @var array<string, string>
     */
    protected $args = [];

    /**
     * Set the arguments to use when generating the final string representation using the format string.
     *
     * @param array<string, string> $args The arguments.
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
     * @return array<string, string>
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
