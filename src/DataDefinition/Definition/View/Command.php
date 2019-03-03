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
    protected $name;

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
    protected $label;

    /**
     * The description text for the command.
     *
     * @var string
     */
    protected $description;

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
    protected $disabled;

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
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(\ArrayObject $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra(\ArrayObject $extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritdoc}
     */
    public function setDisabled($disabled = true)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
