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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties;

/**
 * Interface PropertyInterface.
 *
 * This interface describes a property information.
 */
interface PropertyInterface
{
    /**
     * Return the name of the property.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the label language key.
     *
     * @param string $value The label value.
     *
     * @return PropertyInterface
     */
    public function setLabel($value);

    /**
     * Return the label of the property.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set the description language key.
     *
     * @param string $value The description text.
     *
     * @return PropertyInterface
     */
    public function setDescription($value);

    /**
     * Return the description of the property.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set the default value of the property.
     *
     * @param mixed $value The default value.
     *
     * @return PropertyInterface
     */
    public function setDefaultValue($value);

    /**
     * Return the default value of the property.
     *
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * Set if the property is excluded from access.
     *
     * @param bool $value The flag.
     *
     * @return PropertyInterface
     */
    public function setExcluded($value);

    /**
     * Determinator if this property is excluded from access.
     *
     * @return bool
     */
    public function isExcluded();

    /**
     * Set the search determinator.
     *
     * @param bool $value The flag.
     *
     * @return PropertyInterface
     */
    public function setSearchable($value);

    /**
     * Determinator if search is enabled on this property.
     *
     * @return bool
     */
    public function isSearchable();

    /**
     * Set filtering determinator.
     *
     * @param bool $value The flag.
     *
     * @return PropertyInterface
     */
    public function setFilterable($value);

    /**
     * Determinator if filtering may be performed on this property.
     *
     * @return bool
     */
    public function isFilterable();

    /**
     * Set the widget type name.
     *
     * @param string $value The type name of the widget.
     *
     * @return PropertyInterface
     */
    public function setWidgetType($value);

    /**
     * Return the widget type name.
     *
     * @return string
     */
    public function getWidgetType();

    /**
     * Set the valid values of this property.
     *
     * @param array $value The options.
     *
     * @return PropertyInterface
     */
    public function setOptions($value);

    /**
     * Return the valid values of this property.
     *
     * @return array|null
     */
    public function getOptions();

    /**
     * Set the explanation language string.
     *
     * @param string $value The explanation text.
     *
     * @return PropertyInterface
     */
    public function setExplanation($value);

    /**
     * Return the explanation of the property.
     *
     * @return string
     */
    public function getExplanation();

    /**
     * Set the extra data of the property.
     *
     * @param array $value The extra data for this property.
     *
     * @return PropertyInterface
     */
    public function setExtra($value);

    /**
     * Fetch the extra data of the property.
     *
     * @return array
     */
    public function getExtra();
}
