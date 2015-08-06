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
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;

/**
 * Class ExtendedDca.
 *
 * This interface holds information that extends the default DC_Table compatible definition.
 * It holds reference of which classes to use for the controller, the view and the callbacks.
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
