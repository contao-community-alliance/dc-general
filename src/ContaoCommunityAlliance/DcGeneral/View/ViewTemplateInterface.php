<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View;

/**
 * This interface describes a view template.
 *
 * @package DcGeneral\View
 */
interface ViewTemplateInterface
{
    /**
     * Set the template data from an array.
     *
     * @param array $data The data array.
     *
     * @return ViewTemplateInterface
     */
    public function setData($data);

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
     *
     * @param mixed  $value The value to add to the template.
     *
     * @return ViewTemplateInterface
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
