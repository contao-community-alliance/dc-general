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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * The model formatter format a model and create a string representation.
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
