<?php
/**
 * PHP version 5
 *
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
class PropertyValueInFilterBuilder extends BaseFilterBuilder
{

    /**
     * The property to be checked.
     *
     * @var string
     */
    protected $property;

    /**
     * The value to compare against.
     *
     * @var mixed
     */
    protected $values;

    /**
     * Create a new instance.
     *
     * @param string $property The property name to be compared.
     *
     * @param mixed  $values   The value to be compared against.
     */
    public function __construct($property, $values)
    {
        $this->operation = 'IN';
        $this
            ->setProperty($property)
            ->setValues($values);
    }

    /**
     * Serialize the filter into an array.
     *
     * @return array
     */
    public function get()
    {
        return array(
            'property'  => $this->getProperty(),
            'operation' => 'IN',
            'values'    => $this->getValues()
        );
    }

    /**
     * Set the property name.
     *
     * @param string $property The property name.
     *
     * @return PropertyValueInFilterBuilder
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Retrieve the property name.
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set the value to filter for.
     *
     * @param mixed $values The value.
     *
     * @return PropertyValueInFilterBuilder
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Retrieve the value to filter for.
     *
     * @return mixed
     */
    public function getValues()
    {
        return $this->values;
    }
}
