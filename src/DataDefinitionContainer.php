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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of the data definition container.
 *
 * This container holds all created data definitions.
 */
class DataDefinitionContainer implements DataDefinitionContainerInterface
{
    /**
     * The definitions stored in the container.
     *
     * @var array<string, ContainerInterface>
     */
    protected $definitions = [];

    /**
     * {@inheritDoc}
     */
    public function setDefinition($name, $definition)
    {
        if (null !== $definition) {
            $this->definitions[$name] = $definition;
        } else {
            unset($this->definitions[$name]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasDefinition($name)
    {
        return isset($this->definitions[$name]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a definition is requested that is not contained.
     */
    public function getDefinition($name)
    {
        if (!$this->hasDefinition($name)) {
            throw new DcGeneralInvalidArgumentException('Data definition ' . $name . ' is not contained.');
        }

        return $this->definitions[$name];
    }
}
