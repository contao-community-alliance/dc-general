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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;

/**
 * Class GetOperationButtonEvent.
 *
 * This event gets emitted when an operation button is rendered.
 */
class GetOperationButtonEvent extends BaseButtonEvent
{
    const NAME = 'dc-general.view.contao2backend.get-operation-button';

    /**
     * The command for which the button is being rendered.
     *
     * @var CommandInterface
     */
    protected $command;

    /**
     * The model to which the command shall be applied to.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * The ids of any child records of the model.
     *
     * @var array
     */
    protected $childRecordIds;

    /**
     * Determinator if there is a circular reference from an item in the clipboard to the current model.
     *
     * @var bool
     */
    protected $circularReference;

    /**
     * The next model succeeding the current model.
     *
     * @var ModelInterface
     */
    protected $next;

    /**
     * The model preceeding the current model.
     *
     * @var ModelInterface
     */
    protected $previous;

    /**
     * The href for the command.
     *
     * @var string
     */
    protected $href;

    /**
     * Disabled state of the button.
     *
     * @var bool
     */
    protected $disabled;

    /**
     * Set the attached command.
     *
     * @param CommandInterface $objCommand The command.
     *
     * @return $this
     */
    public function setCommand($objCommand)
    {
        $this->command = $objCommand;

        return $this;
    }

    /**
     * Retrieve the attached command.
     *
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set the ids of the child records of the current model.
     *
     * @param array $childRecordIds The list of ids.
     *
     * @return $this
     */
    public function setChildRecordIds($childRecordIds)
    {
        $this->childRecordIds = $childRecordIds;

        return $this;
    }

    /**
     * Retrieve the ids of the child records of the current model.
     *
     * @return array
     */
    public function getChildRecordIds()
    {
        return $this->childRecordIds;
    }

    /**
     * Set determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @param boolean $circularReference The flag.
     *
     * @return $this
     */
    public function setCircularReference($circularReference)
    {
        $this->circularReference = $circularReference;

        return $this;
    }

    /**
     * Get determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @return boolean
     */
    public function isCircularReference()
    {
        return $this->circularReference;
    }

    /**
     * Set the next model in the list, succeeding the current model.
     *
     * @param ModelInterface $next The successor.
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get the next model in the list, succeeding the current model.
     *
     * @return ModelInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set the previous model in the list, preceding the current model.
     *
     * @param ModelInterface $previous The id of the predecessor.
     *
     * @return $this
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * Get the previous model in the list, preceding the current model.
     *
     * @return ModelInterface
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Set the href for the button.
     *
     * @param string $href The href.
     *
     * @return $this
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Retrieve the href for the button.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set the model currently in scope.
     *
     * @param ModelInterface $model The model.
     *
     * @return $this
     */
    public function setObjModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the model currently in scope.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the button enabled or disabled (true means disabled).
     *
     * @param boolean $disabled The flag.
     *
     * @return $this
     */
    public function setDisabled($disabled = true)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Determine if the command is disabled.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
