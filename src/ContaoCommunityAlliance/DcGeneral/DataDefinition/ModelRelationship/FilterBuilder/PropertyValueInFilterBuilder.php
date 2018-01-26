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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
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
     * Initialize an instance with the values from the given array.
     *
     * @param array $array The initialization array.
     *
     * @return mixed
     *
     * @throws DcGeneralInvalidArgumentException When an invalid array has been passed.
     */
    public static function fromArray($array)
    {
        $values   = $array['value'];
        $property = $array['property'];

        if (!(isset($values) && isset($property))) {
            throw new DcGeneralInvalidArgumentException('Invalid filter array provided  ' . var_export($array, true));
        }

        return new static($property, $values);
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
