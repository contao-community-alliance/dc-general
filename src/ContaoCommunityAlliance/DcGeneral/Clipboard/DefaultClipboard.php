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

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

/**
 * Class DefaultClipboard.
 *
 * Default implementation of the clipboard.
 *
 * @package DcGeneral\Clipboard
 */
class DefaultClipboard implements ClipboardInterface
{
    /**
     * The ids contained.
     *
     * @var array
     */
    protected $arrIds = array();

    /**
     * The ids that will create a circular reference and therefore shall get ignored for pasting.
     *
     * @var array
     */
    protected $arrCircularIds = array();

    /**
     * The current mode the clipboard is in.
     *
     * @var string
     */
    protected $mode;

    /**
     * The id of the parent element for create mode.
     * @var
     */
    protected $parentId;

    /**
     * {@inheritDoc}
     */
    public function loadFrom($objEnvironment)
    {
        $strName      = $objEnvironment->getDataDefinition()->getName();
        $arrClipboard = $objEnvironment->getInputProvider()->getPersistentValue('CLIPBOARD');

        if (isset($arrClipboard[$strName]))
        {
            if (isset($arrClipboard[$strName]['ignoredIDs']))
            {
                $this->setCircularIds($arrClipboard[$strName]['ignoredIDs']);
            }

            if (isset($arrClipboard[$strName]['ids']))
            {
                $this->setContainedIds($arrClipboard[$strName]['ids']);
            }

            if (isset($arrClipboard[$strName]['mode']))
            {
                $this->mode = $arrClipboard[$strName]['mode'];
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function saveTo($objEnvironment)
    {
        $strName      = $objEnvironment->getDataDefinition()->getName();
        $arrClipboard = $objEnvironment->getInputProvider()->getPersistentValue('CLIPBOARD');

        if ($this->isEmpty())
        {
            unset($arrClipboard[$strName]);
        }
        else
        {
            $arrClipboard[$strName] = array();
            if ($this->getCircularIds())
            {
                $arrClipboard[$strName]['ignoredIDs'] = $this->getCircularIds();
            }

            if ($this->isNotEmpty())
            {
                $arrClipboard[$strName]['ids'] = $this->getContainedIds();
            }

            $arrClipboard[$strName]['mode'] = $this->mode;
        }

        $objEnvironment->getInputProvider()->setPersistentValue('CLIPBOARD', $arrClipboard);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        unset($this->arrIds);
        unset($this->mode);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return ((!isset($this->arrIds) || empty($this->arrIds)) && (!isset($this->mode)) || empty($this->mode));
    }

    /**
     * {@inheritDoc}
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * {@inheritDoc}
     */
    public function isCut()
    {
        return $this->mode == self::MODE_CUT;
    }

    /**
     * {@inheritDoc}
     */
    public function isCopy()
    {
        return $this->mode == self::MODE_COPY;
    }

    /**
     * {@inheritDoc}
     */
    public function isCreate()
    {
        return $this->mode == self::MODE_CREATE;
    }

    /**
     * {@inheritDoc}
     */
    public function copy($ids)
    {
        $this->mode = self::MODE_COPY;

        if (is_array($ids) || is_null($ids))
        {
            $this->setContainedIds($ids);
        }
        else
        {
            $this->setContainedIds(array($ids));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function cut($ids)
    {
        $this->mode = self::MODE_CUT;

        if (is_array($ids) || is_null($ids))
        {
            $this->setContainedIds($ids);
        }
        else
        {
            $this->setContainedIds(array($ids));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function create($parentId)
    {
        $this->mode = self::MODE_CREATE;

        $this->setContainedIds(array(null));
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainedIds($arrIds)
    {
        $this->arrIds = $arrIds;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContainedIds()
    {
        return $this->arrIds;
    }

    /**
     * {@inheritDoc}
     */
    public function setCircularIds($arrIds)
    {
        $this->arrCircularIds = (array)$arrIds;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCircularIds()
    {
        return $this->arrCircularIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return $this->isCreate() ? $this->parentId : null;
    }
}
