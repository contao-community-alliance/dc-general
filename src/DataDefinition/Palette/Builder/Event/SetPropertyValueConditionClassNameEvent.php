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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * This event gets emitted when a property value condition class name is set.
 */
class SetPropertyValueConditionClassNameEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.set-property-value-condition-class-name';

    /**
     * The class name.
     *
     * @var class-string<PropertyConditionInterface>
     */
    protected $className;

    /**
     * Create a new instance.
     *
     * @param class-string<PropertyConditionInterface> $className      The class name.
     * @param PaletteBuilder                           $paletteBuilder The palette builder in use.
     */
    public function __construct($className, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyValueConditionClassName($className);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param class-string<PropertyConditionInterface> $className The class name.
     *
     * @return SetPropertyValueConditionClassNameEvent
     */
    public function setPropertyValueConditionClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return class-string<PropertyConditionInterface>
     */
    public function getPropertyValueConditionClassName()
    {
        return $this->className;
    }
}
