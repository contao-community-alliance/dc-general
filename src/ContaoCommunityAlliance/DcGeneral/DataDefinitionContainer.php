<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of the data definition container.
 *
 * This container holds all created data definitions.
 *
 * @package DcGeneral
 */
class DataDefinitionContainer implements DataDefinitionContainerInterface
{
    /**
     * The definitions stored in the container.
     *
     * @var ContainerInterface[]
     */
    protected $definitions;

    /**
     * {@inheritDoc}
     */
    public function setDefinition($name, $definition)
    {
        if ($definition) {
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
