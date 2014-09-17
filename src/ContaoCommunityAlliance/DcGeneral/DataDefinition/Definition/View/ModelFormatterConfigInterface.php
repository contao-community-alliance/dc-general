<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * The model formatter format a model and create a string representation.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
interface ModelFormatterConfigInterface
{
    /**
     * Set the used property names.
     *
     * @param array $propertyNames The property names.
     *
     * @return ModelFormatterConfigInterface
     */
    public function setPropertyNames(array $propertyNames);

    /**
     * Return the used property names.
     *
     * @return array
     */
    public function getPropertyNames();

    /**
     * Set the format string.
     *
     * @param string $format The format string to use.
     *
     * @return ModelFormatterConfigInterface
     */
    public function setFormat($format);

    /**
     * Return the format string.
     *
     * @return string
     */
    public function getFormat();

    /**
     * Set the formatted maximum length.
     *
     * @param int|null $maxLength The length to use - pass null to clear the cutting.
     *
     * @return ModelFormatterConfigInterface
     */
    public function setMaxLength($maxLength);

    /**
     * Return the formatted maximum length.
     *
     * @return int|null
     */
    public function getMaxLength();
}
