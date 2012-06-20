<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/GPL 2
 * @filesource
 */

/**
 * Class ViewBuilder
 */
class ViewBuilder extends Backend
{

    /**
     * Contains data container
     * @var DC_General
     */
    protected $objDc;
    
    /**
     * button id
     * @var string
     */
    protected $strBid;
    
    /**
     * Flag for input select
     * @var boolean
     */
    protected $blnSelect;

    /**
     * Initialize the object
     */
    public function __construct(DC_General $objDc)
    {
        parent::__construct();

        $this->objDc = $objDc;
        $this->blnSelect = ($this->Input->get('act') == 'select') ? TRUE : FALSE;
        
        $this->displayButtons();
    }

    /**
     * Generate list view from current collection
     * 
     * @return string 
     */
    public function listView()
    {        
        $arrDCA = $this->objDc->getDCA();        
        $arrReturn = array();
        
        // Add display buttons
        $arrReturn[] = $this->displayButtons();
        
        // Generate buttons
        foreach($this->objDc->getCurrentCollecion() as $objModelRow)
        {
            $arrView = $objModelRow->getProperty('view');
            $arrView['buttons'] = $this->generateButtons($objModelRow, $this->objDc->getTable(), $this->objDc->getRootIds());            
            $objModelRow->setProperty('view', $arrView); 
        }
        
        // Add template
        $objTemplate = new BackendTemplate('be_general_showAll');
        $objTemplate->collection = $this->objDc->getCurrentCollecion();
        $objTemplate->select = $this->blnSelect;
        $objTemplate->action = ampersand($this->Environment->request, true);
        $objTemplate->mode = $arrDCA['list']['sorting']['mode'];
        $objTemplate->notDeletable = $arrDCA['config']['notDeletable'];
        $objTemplate->notEditable = $arrDCA['config']['notEditable'];
        $arrReturn[] = $objTemplate->parse();

        return implode('', $arrReturn);
    }
    
    public function panel()
    {
        $arrDCA = $this->objDc->getDCA();
        $arrReturn = array();
        
        if(is_array($this->objDc->getPanelView()) && count($this->objDc->getPanelView()) > 0)
        {        
            $objTemplate = new BackendTemplate('be_general_panel');
            $objTemplate->action = ampersand($this->Environment->request, true);
            $objTemplate->theme = $this->getTheme();
            $objTemplate->panel = $this->objDc->getPanelView();
            $arrReturn[] = $objTemplate->parse();
        }
        
        return implode('', $arrReturn);        
    }

    /**
     * Generate header display buttons
     * 
     * @return string 
     */
    public function displayButtons()
    {
        $arrDCA = $this->objDc->getDCA();

        $arrReturn = array();
        if (!$arrDCA['config']['closed'] || count($arrDCA['list']['global_operations']))
        {
            // Add open wrapper
            $arrReturn[] = '<div id="' . $this->objDc->getButtonId() . '">';

            // Add back button
            $arrReturn[] = (($this->blnSelect || $this->objDc->getParentTable()) ? '<a href="' . $this->getReferer(true, $this->objDc->getParentTable()) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' : '');

            // Add divider
            $arrReturn[] = (($this->objDc->getParentTable() && !$this->blnSelect) ? ' &nbsp; :: &nbsp;' : '');

            if (!$this->blnSelect)
            {
                // Add new button
                $arrReturn[] = ' ' . (!$arrDCA['config']['closed'] ? '<a href="' . (strlen($this->objDc->getParentTable()) ? $this->addToUrl('act=create' . (($arrDCA['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->objDc->getId()) : $this->addToUrl('act=create')) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->objDc->getTable()]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG'][$this->objDc->getTable()]['new'][0] . '</a>' : '');

                // Add global buttons
                $arrReturn[] = $this->generateGlobalButtons();
            }

            // Add close wrapper
            $arrReturn[] = '</div>';

            $arrReturn[] = $this->getMessages(true);
        }

        return implode('', $arrReturn);
    }

    /**
     * Compile buttons from the table configuration array and return them as HTML
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @param string $strTable
     * @param array $arrRootIds
     * @param boolean $blnCircularReference
     * @param array $arrChildRecordIds
     * @param int $strPrevious
     * @param int $strNext
     * @return string
     */
    public function generateButtons(InterfaceGeneralModel $objModelRow, $strTable, $arrRootIds = array(), $blnCircularReference = false, $arrChildRecordIds = null, $strPrevious = null, $strNext = null)
    {
        if (!count($GLOBALS['TL_DCA'][$strTable]['list']['operations']))
        {
            return '';
        }

        $return = '';

        foreach ($GLOBALS['TL_DCA'][$strTable]['list']['operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label = strlen($v['label'][0]) ? $v['label'][0] : $k;
            $title = sprintf((strlen($v['label'][1]) ? $v['label'][1] : $k), $objModelRow->getProperty('id'));
            $attributes = strlen($v['attributes']) ? ' ' . ltrim(sprintf($v['attributes'], $objModelRow->getProperty('id'), $objModelRow->getProperty('id'))) : '';

            // Call a custom function instead of using the default button
            if (is_array($v['button_callback']))
            {
                $this->import($v['button_callback'][0]);
                $return .= $this->$v['button_callback'][0]->$v['button_callback'][1]($objModelRow->getPropertiesAsArray(), $v['href'], $label, $title, $v['icon'], $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);

                continue;
            }

            // Generate all buttons except "move up" and "move down" buttons
            if ($k != 'move' && $v != 'move')
            {
                $return .= '<a href="' . $this->addToUrl($v['href'] . '&amp;id=' . $objModelRow->getProperty('id')) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($v['icon'], $label) . '</a> ';
                continue;
            }

            $arrDirections = array('up', 'down');
            $arrRootIds = is_array($arrRootIds) ? $arrRootIds : array($arrRootIds);

            foreach ($arrDirections as $dir)
            {
                $label = strlen($GLOBALS['TL_LANG'][$strTable][$dir][0]) ? $GLOBALS['TL_LANG'][$strTable][$dir][0] : $dir;
                $title = strlen($GLOBALS['TL_LANG'][$strTable][$dir][1]) ? $GLOBALS['TL_LANG'][$strTable][$dir][1] : $dir;

                $label = $this->generateImage($dir . '.gif', $label);
                $href = strlen($v['href']) ? $v['href'] : '&amp;act=move';

                if ($dir == 'up')
                {
                    $return .= ((is_numeric($strPrevious) && (!in_array($objModelRow->getProperty('id'), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getProperty('id')) . '&amp;sid=' . intval($strPrevious) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('up_.gif')) . ' ';
                    continue;
                }

                $return .= ((is_numeric($strNext) && (!in_array($objModelRow->getProperty('id'), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getProperty('id')) . '&amp;sid=' . intval($strNext) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('down_.gif')) . ' ';
            }
        }

        return trim($return);
    }

    /**
     * Compile global buttons from the table configuration array and return them as HTML
     * 
     * @param boolean $blnForceSeparator
     * @return string
     */
    protected function generateGlobalButtons($blnForceSeparator = false)
    {
        $arrDCA = $this->objDc->getDCA();

        if (!is_array($arrDCA['list']['global_operations']))
        {
            return '';
        }

        $return = '';
        foreach ($arrDCA['list']['global_operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label = is_array($v['label']) ? $v['label'][0] : $v['label'];
            $title = is_array($v['label']) ? $v['label'][1] : $v['label'];
            $attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

            if (!strlen($label))
            {
                $label = $k;
            }

            // Call a custom function instead of using the default button
            if (is_array($v['button_callback']))
            {
                $this->import($v['button_callback'][0]);
                $return .= $this->$v['button_callback'][0]->$v['button_callback'][1]($v['href'], $label, $title, $v['icon'], $attributes, $this->strTable, $this->root);

                continue;
            }

            $return .= ' &#160; :: &#160; <a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
        }

        return ($arrDCA['config']['closed'] && !$blnForceSeparator) ? preg_replace('/^ &#160; :: &#160; /', '', $return) : $return;
    }
    
}

?>