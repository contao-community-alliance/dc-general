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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * This interface holds the information about the characteristics of a root model.
 */
interface RootConditionInterface
{
    /**
     * Set the condition as filter.
     *
     * @param array $value The filter rules to be used for finding root models.
     *
     * @return RootConditionInterface
     */
    public function setFilterArray($value);

    /**
     * Get the condition as filter.
     *
     * @return array
     */
    public function getFilterArray();

    /**
     * Set the condition setters.
     *
     * @param array $value The values to be used when making a model a root model.
     *
     * @return RootConditionInterface
     */
    public function setSetters($value);

    /**
     * Get the condition setters.
     *
     * @return array
     */
    public function getSetters();

    /**
     * Set the name of the source provider.
     *
     * @param string $value The data provider name.
     *
     * @return RootConditionInterface
     */
    public function setSourceName($value);

    /**
     * Return the name of the source provider.
     *
     * @return string
     */
    public function getSourceName();

    /**
     * Apply the root condition to a model.
     *
     * @param ModelInterface $objModel The model that shall become a root model.
     *
     * @return RootConditionInterface
     */
    public function applyTo($objModel);

    /**
     * Test if the given model is indeed a root object according to this condition.
     *
     * @param ModelInterface $objModel The model to be tested.
     *
     * @return bool
     */
    public function matches($objModel);
}
