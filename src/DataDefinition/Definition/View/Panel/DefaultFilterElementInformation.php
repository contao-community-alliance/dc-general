<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultFilterElementInformation.
 *
 * Default implementation of a filter definition for a property.
 */
class DefaultFilterElementInformation implements FilterElementInformationInterface
{
    /**
     * The name of the property to filter on.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return sprintf('filter[%s]', $this->getPropertyName());
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
