<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This class defines a collection of grouping and sorting information for the view.
 */
class DefaultGroupAndSortingDefinition implements GroupAndSortingDefinitionInterface
{
    /**
     * The information stored.
     *
     * @var GroupAndSortingInformationInterface[]
     */
    protected $information = array();

    /**
     * The name of the definition.
     *
     * @var string
     */
    protected $name = '';

    /**
     * {@inheritDoc}
     */
    public function add($index = -1)
    {
        $information = new DefaultGroupAndSortingInformation();

        if (($index < 0) || ($this->getCount() <= $index)) {
            $this->information[] = $information;
        } else {
            array_splice($this->information, $index, 0, array($information));
        }

        return $information;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($index)
    {
        unset($this->information[$index]);
        $this->information = array_values($this->information);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount()
    {
        return count($this->information);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException when the given offset does not exist.
     */
    public function get($index)
    {
        if (!isset($this->information[$index])) {
            throw new DcGeneralInvalidArgumentException('Offset ' . $index . ' does not exist.');
        }

        return $this->information[$index];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->information);
    }
}
