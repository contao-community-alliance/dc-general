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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultSearchElementInformation.
 *
 * Default implementation of a search definition on properties.
 */
class DefaultSearchElementInformation implements SearchElementInformationInterface
{
    /**
     * The property names to search on.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'search';
    }

    /**
     * {@inheritDoc}
     */
    public function addProperty($propertyName)
    {
        $this->properties[] = $propertyName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames()
    {
        return $this->properties;
    }
}
