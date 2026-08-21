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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A property contained within a palette.
 *
 * @api
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
     * @var PropertyConditionInterface|null
     */
    protected $visibleCondition = null;

    /**
     * The condition to be examined to determine if this property is editable.
     *
     * @var PropertyConditionInterface|null
     */
    protected $editableCondition = null;

    /**
     * The property names currently being resolved for visibility, keyed by object id, in call
     * order. Detects a visible-condition cycle (property A depends on B depends on A) before it
     * exhausts the call stack - see contao-community-alliance/dc-general#528.
     *
     * @var array<int, string>
     */
    private static array $visibilityGuard = [];

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
    public function isVisible(
        ?ModelInterface $model = null,
        ?PropertyValueBagInterface $input = null,
        ?LegendInterface $legend = null
    ) {
        if ($this->visibleCondition) {
            // We should have defined the interfaces back in 2013... :/
            assert($input === null || $input instanceof PropertyValueBag);

            $key = spl_object_id($this);
            if (isset(self::$visibilityGuard[$key])) {
                $chain = implode('" -> "', [...array_values(self::$visibilityGuard), $this->name]);
                throw new DcGeneralRuntimeException('Circular visible condition detected: "' . $chain . '"');
            }

            self::$visibilityGuard[$key] = $this->name;
            try {
                return $this->visibleCondition->match($model, $input, $this, $legend);
            } finally {
                unset(self::$visibilityGuard[$key]);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isEditable(
        ?ModelInterface $model = null,
        ?PropertyValueBagInterface $input = null,
        ?LegendInterface $legend = null
    ) {
        if ($this->editableCondition) {
            // We should have defined the interfaces back in 2013... :/
            assert($input === null || $input instanceof PropertyValueBag);
            return $this->editableCondition->match($model, $input, $this, $legend);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setVisibleCondition(?PropertyConditionInterface $condition = null)
    {
        $this->visibleCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVisibleCondition()
    {
        return $this->visibleCondition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setEditableCondition(?PropertyConditionInterface $condition = null)
    {
        $this->editableCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEditableCondition()
    {
        return $this->editableCondition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
