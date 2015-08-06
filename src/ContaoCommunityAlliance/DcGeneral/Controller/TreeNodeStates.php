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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

/**
 * Wrapper class for tree node states.
 *
 * This class encapsulates the open/closed states for a tree of models.
 */
class TreeNodeStates
{
    /**
     * The states of the nodes.
     *
     * @var array
     */
    private $states;

    /**
     * List of implicit open nodes (selected values i.e.).
     *
     * @var array
     */
    private $implicitOpen;

    /**
     * Create a new instance.
     *
     * @param array $states       The initial state array (optional, if not given, the state information will be empty).
     *
     * @param array $implicitOpen List of implicit open nodes (selected values, if not given, the state information will
     *                            be empty).
     */
    public function __construct($states = array(), $implicitOpen = array())
    {
        $this->setStates($states);
        $this->setImplicitOpen($implicitOpen);
    }

    /**
     * Set the native states.
     *
     * The states must be created via the getStates() method.
     *
     * @param array $states The state array.
     *
     * @return TreeNodeStates
     *
     * @see    getStates()
     */
    public function setStates($states)
    {
        $this->states = (array) $states;

        return $this;
    }

    /**
     * Retrieve the states as array.
     *
     * The returned array may be stored to session or other persistent storage and imported again via constructor
     * argument or a call to setStates().
     *
     * @return array
     *
     * @see    getStates().
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Set the list of implicit open nodes.
     *
     * @param array $implicitOpen The state array.
     *
     * @return TreeNodeStates
     */
    public function setImplicitOpen($implicitOpen)
    {
        $this->implicitOpen = (array) $implicitOpen;

        return $this;
    }

    /**
     * Retrieve the list of implicit open nodes.
     *
     * The returned array may be stored to session or other persistent storage and imported again via constructor
     * argument or a call to setStates().
     *
     * @return array
     */
    public function getImplicitOpen()
    {
        return $this->implicitOpen;
    }

    /**
     * Retrieve the flag if all nodes shall be shown in "open" state.
     *
     * @return boolean
     */
    public function isAllOpen()
    {
        return (bool) $this->states['all'];
    }

    /**
     * Set the flag if all elements shall be expanded or not.
     *
     * If this flag is set, all tree nodes will be handled in "open" state - if this flag is not set, the visibility
     * states will get read from the open flags per driver.
     *
     * @param boolean $allOpen The flag.
     *
     * @return TreeNodeStates
     */
    public function setAllOpen($allOpen)
    {
        $this->states['all'] = (bool) $allOpen;

        return $this;
    }

    /**
     * This will reset all the information and therefore close all models.
     *
     * @return TreeNodeStates
     */
    public function resetAll()
    {
        $this->states       = array('all' => $this->isAllOpen());
        $this->implicitOpen = array();

        return $this;
    }

    /**
     * Determine if the model is expanded.
     *
     * @param string $providerName   The data provider name.
     *
     * @param mixed  $modelId        The id of the model.
     *
     * @param bool   $ignoreAllState If this is true, the "all open" flag will be ignored.
     *
     * @return bool
     */
    public function isModelOpen($providerName, $modelId, $ignoreAllState = false)
    {
        if (!$ignoreAllState && isset($this->states['all']) && ($this->states['all'] == 1)) {
            return true;
        }

        return (isset($this->states[$providerName][$modelId]) && ($this->states[$providerName][$modelId]))
            || (isset($this->implicitOpen[$providerName][$modelId]) && ($this->implicitOpen[$providerName][$modelId]));
    }

    /**
     * Toggle the model with the given id from the given provider.
     *
     * @param string $providerName The data provider name.
     *
     * @param mixed  $modelId      The id of the model.
     *
     * @return TreeNodeStates
     */
    public function toggleModel($providerName, $modelId)
    {
        if (!isset($this->states[$providerName])) {
            $this->states[$providerName] = array();
        }

        return $this->setModelState($providerName, $modelId, !$this->isModelOpen($providerName, $modelId, true));
    }

    /**
     * Toggle the model with the given id from the given provider.
     *
     * @param string $providerName The data provider name.
     *
     * @param mixed  $modelId      The id of the model.
     *
     * @param bool   $state        The new state for the model.
     *
     * @return TreeNodeStates
     */
    public function setModelState($providerName, $modelId, $state)
    {
        if (!isset($this->states[$providerName])) {
            $this->states[$providerName] = array();
        }

        $this->states[$providerName][$modelId] = (bool) $state;

        return $this;
    }
}
