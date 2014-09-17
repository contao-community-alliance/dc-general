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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;

/**
 * Class ExtendedDca.
 *
 * This interface holds information that extends the default DC_Table compatible definition.
 * It holds reference of which classes to use for the controller, the view and the callbacks.
 *
 * @package DcGeneral\Contao\Dca\Definition
 */
class ExtendedDca implements DefinitionInterface
{
    const NAME = 'extended-dca';

    /**
     * Controller class to use.
     *
     * @var string
     */
    protected $controllerClass;

    /**
     * View class to use.
     *
     * @var string
     */
    protected $viewClass;

    /**
     * Set the class name of the controller class.
     *
     * @param string $controllerClass The class name.
     *
     * @return void
     */
    public function setControllerClass($controllerClass)
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * Get the class name of the controller class.
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * Set the class name of the view class.
     *
     * @param string $viewClass The class name.
     *
     * @return void
     */
    public function setViewClass($viewClass)
    {
        $this->viewClass = $viewClass;
    }

    /**
     * Get the class name of the view class.
     *
     * @return string
     */
    public function getViewClass()
    {
        return $this->viewClass;
    }
}
