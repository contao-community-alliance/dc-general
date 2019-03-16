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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This event gets emitted when a property is finished.
 */
class FinishPropertyEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.finish-property';

    /**
     * The property.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * Create a new instance.
     *
     * @param PropertyInterface $property       The property.
     * @param PaletteBuilder    $paletteBuilder The palette builder in use.
     */
    public function __construct(PropertyInterface $property, PaletteBuilder $paletteBuilder)
    {
        $this->setProperty($property);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property.
     *
     * @param PropertyInterface $property The property.
     *
     * @return FinishPropertyEvent
     */
    public function setProperty(PropertyInterface $property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Retrieve the property.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }
}
