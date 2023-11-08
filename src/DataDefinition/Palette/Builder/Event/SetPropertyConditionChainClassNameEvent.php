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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * This event gets emitted when a property condition chain class name is set.
 *
 * @psalm-type TConditionInterface=ConditionChainInterface&PropertyConditionInterface
 *
 */
class SetPropertyConditionChainClassNameEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.set-property-condition-chain-class-name';

    /**
     * The class name.
     *
     * @var class-string<TConditionInterface>
     */
    protected $className;

    /**
     * Create a new instance.
     *
     * @param class-string<TConditionInterface> $className The class name.
     * @param PaletteBuilder                    $paletteBuilder The palette builder in use.
     */
    public function __construct($className, PaletteBuilder $paletteBuilder)
    {
        $this->setPalettePropertyConditionChainClassName($className);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param class-string<TConditionInterface> $className The class name.
     *
     * @return SetPropertyConditionChainClassNameEvent
     */
    public function setPalettePropertyConditionChainClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return class-string<TConditionInterface>
     */
    public function getPalettePropertyConditionChainClassName()
    {
        return $this->className;
    }
}
