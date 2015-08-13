<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a property class name is set.
 */
class SetPropertyClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-property-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $propertyClassName;

    /**
     * Create a new instance.
     *
     * @param string         $propertyClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder    The palette builder in use.
     */
    public function __construct($propertyClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyClassName($propertyClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $propertyClassName The class name.
     *
     * @return SetPropertyClassNameEvent
     */
    public function setPropertyClassName($propertyClassName)
    {
        $this->propertyClassName = (string) $propertyClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPropertyClassName()
    {
        return $this->propertyClassName;
    }
}
