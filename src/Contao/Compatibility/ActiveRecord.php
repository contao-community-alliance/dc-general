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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Compatibility;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * Small compatibility layer for the $dc->activeRecord property.
 */
class ActiveRecord
{
    /**
     * The underlying model.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new instance.
     *
     * @param ModelInterface $model The model.
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Magic getter.
     *
     * @param string $name The name of the property to get.
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->model->getProperty($name);
    }

    /**
     * Magic setter.
     *
     * @param string $name  The name of the property to set.
     * @param mixed  $value The value of the property to set.
     *
     */
    public function __set($name, $value)
    {
        $this->model->setProperty($name, $value);
    }

    /**
     * Return the underlying model.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
