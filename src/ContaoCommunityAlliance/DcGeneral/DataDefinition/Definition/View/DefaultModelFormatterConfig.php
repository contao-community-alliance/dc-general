<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Format a model and create a listing child record.
 */
class DefaultModelFormatterConfig implements ModelFormatterConfigInterface
{
    /**
     * The used property names.
     *
     * @var array
     */
    protected $propertyNames = array();

    /**
     * The format string.
     *
     * @var string
     */
    protected $format = '%s';

    /**
     * The maximum length of the formatted string.
     *
     * @var int|null
     */
    protected $maxLength = null;

    /**
     * {@inheritDoc}
     */
    public function setPropertyNames(array $propertyNames)
    {
        $this->propertyNames = $propertyNames;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames()
    {
        return $this->propertyNames;
    }

    /**
     * {@inheritDoc}
     */
    public function setFormat($format)
    {
        $this->format = (string) $format;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = ($maxLength !== null) ? (int) $maxLength : null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }
}
