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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This is the default implementation of a collection of data provider information.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 */
class DefaultDataProviderDefinition implements DataProviderDefinitionInterface
{
    /**
     * The data provider information stored in the definition.
     *
     * @var array<string, DataProviderInformationInterface>
     */
    protected $information = [];

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When an invalid instance has been passed or a provider definition with
     *                                           the given name has already been registered.
     */
    public function addInformation($information)
    {
        if (!($information instanceof DataProviderInformationInterface)) {
            throw new DcGeneralInvalidArgumentException('Invalid value passed.');
        }

        $name = $information->getName();

        if ($this->hasInformation($name)) {
            throw new DcGeneralInvalidArgumentException('Data provider name ' . $name . ' already registered.');
        }

        $this->information[$name] = $information;

        return $this;
    }

    /**
     * Convert a value into a data definition name.
     *
     * Convenience method to ensure we have a data provider name.
     *
     * @param DataProviderInformationInterface|string $information The information or name of a data provider.
     *
     * @return string
     *
     * @throws DcGeneralInvalidArgumentException If neither a string nor an instance of DataProviderInformationInterface
     *                                           has been passed.
     *
     * @internal
     */
    protected function makeName($information)
    {
        if ($information instanceof DataProviderInformationInterface) {
            $information = $information->getName();
        }

        return $information;
    }

    /**
     * {@inheritdoc}
     */
    public function removeInformation($information)
    {
        unset($this->information[$this->makeName($information)]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setInformation($name, $information)
    {
        $this->information[$name] = $information;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInformation($information)
    {
        return \array_key_exists($this->makeName($information), $this->information);
    }

    /**
     * {@inheritdoc}
     */
    public function getInformation($information)
    {
        return $this->information[$this->makeName($information)];
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderNames()
    {
        return \array_keys($this->information);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->information);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->information);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->hasInformation($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): mixed
    {
        return $this->getInformation($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        assert(\is_string($offset));
        $this->setInformation($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->removeInformation($offset);
    }

    /**
     * @param string $name
     */
    public function __isset($name)
    {
        return $this->hasInformation($name);
    }

    /**
     * @param string $name
     */
    public function __get($name)
    {
        return $this->getInformation($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->setInformation($name, $value);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->removeInformation($name);
    }
}
