<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Compatibility;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * Class ActiveRecord
 *
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
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->model->getProperty($name);
    }

    /**
     * {@inheritdoc}
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
