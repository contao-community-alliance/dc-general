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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties;

/**
 * This interface describes a property information that is aware of an empty value.
 */
interface EmptyValueAwarePropertyInterface
{
    /**
     * Check if the property has an empty value.
     *
     * @return bool
     */
    public function hasEmptyValue();

    /**
     * Retrieve the empty value.
     *
     * @return mixed
     */
    public function getEmptyValue();

    /**
     * Set the empty value.
     *
     * @param mixed $value The value to set.
     *
     * @return static
     */
    public function setEmptyValue($value);

    /**
     * Reset an empty value.
     *
     * @return static
     */
    public function resetEmptyValue();
}
