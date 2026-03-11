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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Class Command.
 *
 * Implementation about a command definition.
 */
class Command implements CommandInterface
{
    /**
     * Name of the command.
     *
     * @var string
     */
    protected $name = '';

    /**
     * The parameters for the command.
     *
     * @var \ArrayObject
     */
    protected $parameters;

    /**
     * The label string for the command.
     *
     * @var string
     */
    protected $label = '';

    /**
     * The description text for the command.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The extra data for the command.
     *
     * @var \ArrayObject
     */
    protected $extra;

    /**
     * Flag if the command is disabled or not.
     *
     * @var bool
     */
    protected $disabled = false;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->parameters = new \ArrayObject();
        $this->extra      = new \ArrayObject();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setParameters(\ArrayObject $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setExtra(\ArrayObject $extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDisabled($disabled = true)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isDisabled()
    {
        return $this->disabled;
    }
}
