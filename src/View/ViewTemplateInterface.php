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

namespace ContaoCommunityAlliance\DcGeneral\View;

/**
 * This interface describes a view template.
 */
interface ViewTemplateInterface
{
    /**
     * Set the template data from an array.
     *
     * @param array $arrData The data array.
     *
     * @return self
     */
    public function setData($arrData);

    /**
     * Return the template data as array.
     *
     * @return array The data array
     */
    public function getData();

    /**
     * Add the value to the template.
     *
     * @param string $name  Name of the value.
     * @param mixed  $value The value to add to the template.
     *
     * @return self
     */
    public function set($name, $value);

    /**
     * Retrieve a value from the template.
     *
     * @param string $name The name of the value to retrieve.
     *
     * @return mixed
     */
    public function get($name);

    /**
     * Parse the template file and return it as string.
     *
     * @return string The template markup
     */
    public function parse();

    /**
     * Parse the template file and print it to the screen.
     *
     * @return void
     */
    public function output();
}
