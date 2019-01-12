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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao;

/**
 * Simulate the contao template class.
 */
class Template
{
    /**
     * Template file
     * @var string
     */
    protected $strTemplate;

    /**
     * Content type
     * @var string
     */
    protected $strContentType;

    /**
     * Create a new template object
     *
     * @param string $strTemplate    The template name
     * @param string $strContentType The content type (defaults to "text/html")
     */
    public function __construct($strTemplate='', $strContentType='text/html')
    {
        $this->strTemplate = $strTemplate;
        $this->strContentType = $strContentType;
    }

    /**
     * Set the template name
     *
     * @param string $strTemplate The template name
     */
    public function setName($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }


    /**
     * Return the template name
     *
     * @return string The template name
     */
    public function getName()
    {
        return $this->strTemplate;
    }
}
