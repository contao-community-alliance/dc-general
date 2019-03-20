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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * A property contained within a palette.
 */
class Property implements PropertyInterface
{
    /**
     * The name of the property.
     *
     * @var string
     */
    protected $name;

    /**
     * The condition to be examined to determine if this property is visible.
     *
     * @var PropertyConditionInterface
     */
    protected $visibleCondition;

    /**
     * The condition to be examined to determine if this property is editable.
     *
     * @var PropertyConditionInterface
     */
    protected $editableCondition;

    /**
     * Create a new instance.
     *
     * @param string $name The name of the property.
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = (string) $name;
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
    public function isVisible(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        LegendInterface $legend = null
    ) {
        if ($this->visibleCondition) {
            return $this->visibleCondition->match($model, $input, $this, $legend);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        LegendInterface $legend = null
    ) {
        if ($this->editableCondition) {
            return $this->editableCondition->match($model, $input, $this, $legend);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibleCondition(PropertyConditionInterface $condition = null)
    {
        $this->visibleCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleCondition()
    {
        return $this->visibleCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditableCondition(PropertyConditionInterface $condition = null)
    {
        $this->editableCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableCondition()
    {
        return $this->editableCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        if (null !== $this->visibleCondition) {
            $this->visibleCondition = clone $this->visibleCondition;
        }
        if (null !== $this->editableCondition) {
            $this->editableCondition = clone $this->editableCondition;
        }
    }
}
