<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This interface is for collect some information from the edit model, and provide some useful stuff for edit/override
 * all.
 */
interface EditInformationInterface
{
    /**
     * Retrieve if any model has an error.
     *
     * @return bool
     */
    public function hasAnyModelError();

    /**
     * Get the model error.
     *
     * @param ModelInterface $model The model.
     *
     * @return array
     */
    public function getModelError(ModelInterface $model);

    /**
     * Set the error information for the model.
     *
     * @param ModelInterface    $model    The model.
     *
     * @param array             $error    The error information.
     *
     * @param PropertyInterface $property The property.
     *
     * @return void
     */
    public function setModelError(ModelInterface $model, array $error, PropertyInterface $property);

    /**
     * Get the uniform time. This is useful for edit many models in one time.
     *
     * @return integer
     */
    public function uniformTime();
}
