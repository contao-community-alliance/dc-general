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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * This class is used for the contao backend view as template.
 *
 * @package DcGeneral\View
 */
class ContaoBackendViewTemplate extends \BackendTemplate implements ViewTemplateInterface
{
    /**
     * {@inheritDoc}
     */
    public function setData($data)
    {
        parent::setData($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        return $this->$name;
    }
}
