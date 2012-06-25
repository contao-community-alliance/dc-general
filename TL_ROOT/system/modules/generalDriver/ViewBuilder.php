<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

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

        $this->dc = $objDc;
        $this->dca = $this->dc->getDCA();
        $this->blnSelect = ($this->Input->get('act') == 'select') ? TRUE : FALSE;
    }

    // View --------------------------------------------------------------------

    /**
     * Generate list view from current collection
     * 
     * @return string 
     */
    public function listView()
    {
        $arrReturn = array();

        // Add display buttons
        if (!$this->dca['config']['closed'] || !empty($this->dca['list']['global_operations']))
        {
            $arrReturn[] = $this->displayButtons($this->dc->getButtonId());
        }

        // Set label
        $this->setLabel();

        // Generate buttons
        foreach ($this->dc->getCurrentCollecion() as $objModelRow)
        {
            $objModelRow->setProperty('%buttons%', $this->generateButtons($objModelRow, $this->dc->getTable(), $this->dc->getRootIds()));
        }

        // Add template
        $objTemplate = new BackendTemplate('dcbe_general_showAll');
        $objTemplate->collection = $this->dc->getCurrentCollecion();
        $objTemplate->select = $this->blnSelect;
        $objTemplate->action = ampersand($this->Environment->request, true);
        $objTemplate->mode = $this->dca['list']['sorting']['mode'];
        $objTemplate->tableHead = $this->getTableHead();
        $objTemplate->notDeletable = $this->dca['config']['notDeletable'];
        $objTemplate->notEditable = $this->dca['config']['notEditable'];
        $arrReturn[] = $objTemplate->parse();

        return implode('', $arrReturn);
    }

    public function parentView()
    {
        // Load language file and data container array of the parent table
        $this->loadLanguageFile($this->dc->getParentTable());
        $this->loadDataContainer($this->dc->getParentTable());

        $objParentDC  = new DC_General($this->dc->getParentTable());
        $arrParentDCA = $objParentDC->getDCA();

        $blnHasSorting = $this->dca['list']['sorting']['fields'][0] == 'sorting';

        $arrReturn = array();

        $arrReturn[] = $this->displayButtons(tl_buttons);

        if ($this->dc->getCurrentParentCollection()->length() == 0)
        {
            return implode('', $arrReturn);
        }

        // Add template
        $objTemplate = new BackendTemplate('dcbe_general_parentView');
        $objTemplate->collection = $this->dc->getCurrentCollecion();
        $objTemplate->select = $this->blnSelect;
        $objTemplate->action = ampersand($this->Environment->request, true);
        $objTemplate->mode = $this->dca['list']['sorting']['mode'];
        $objTemplate->table = $this->dc->getTable();

        $objTemplate->editHeader = array(
            'content' => $this->generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->strTable]['editheader'][0]),
            'href' => preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->dc->getParentTable()) ? '&amp;table=' . $this->dc->getParentTable() : ''), $this->addToUrl('act=edit')),
            'title' => specialchars($GLOBALS['TL_LANG'][$this->dc->getTable()]['editheader'][1])
        );

        $objTemplate->pasteNew = array(
            'content' => $this->generateImage('new.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]),
            'href' => $this->addToUrl('act=create&amp;mode=2&amp;pid='.$objParent->id.'&amp;id='.$this->intId),
            'title' => specialchars($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0])
        );

        $objTemplate->pasteAfter = array(
            'content' => $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0], 'class="blink"'),
            'href' => $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$objParent->id . (!$blnMultiboard ? '&amp;id='.$arrClipboard['id'] : '')),
            'title' => specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0])
        );

        $objTemplate->notDeletable = $this->dca['config']['notDeletable'];
        $objTemplate->notEditable = $this->dca['config']['notEditable'];
        $objTemplate->notEditableParent = $arrParentDCA['config']['notEditable'];
        $arrReturn[] = $objTemplate->parse();

        return implode('', $arrReturn);     
    }

    // Panel -------------------------------------------------------------------

    public function panel()
    {
        $arrReturn = array();

        if (is_array($this->dc->getPanelView()) && count($this->dc->getPanelView()) > 0)
        {
            $objTemplate = new BackendTemplate('dcbe_general_panel');
            $objTemplate->action = ampersand($this->Environment->request, true);
            $objTemplate->theme = $this->getTheme();
            $objTemplate->panel = $this->dc->getPanelView();
            $arrReturn[] = $objTemplate->parse();
        }

        return implode('', $arrReturn);
    }

    // Helper ------------------------------------------------------------------

    protected function getTableHead()
    {
        $arrTableHead = array();

        // Generate the table header if the "show columns" option is active
        if ($this->dca['list']['label']['showColumns'])
        {
            foreach ($this->dca['list']['label']['fields'] as $f)
            {
                $arrTableHead[] = array(
                    'class' => 'tl_folder_tlist col_' . $f . (($f == $this->dc->getFirstSorting()) ? ' ordered_by' : ''),
                    'content' => $this->dca['fields'][$f]['label'][0]
                );
            }

            $arrTableHead[] = array(
                'class' => 'tl_folder_tlist tl_right_nowrap',
                'content' => '&nbsp;'
            );
        }

        return $arrTableHead;
    }

    /**
     * Set label for each model 
     */
    protected function setLabel()
    {
        // Automatically add the "order by" field as last column if we do not have group headers
        if ($this->dca['list']['label']['showColumns'] && !in_array($this->dc->getFirstSorting(), $this->dca['list']['label']['fields']))
        {
            $this->dca['list']['label']['fields'][] = $this->dc->getFirstSorting();
        }

        $remoteCur  = false;
        $groupclass = 'tl_folder_tlist';
        $eoCount    = -1;

        foreach ($this->dc->getCurrentCollecion() as $objModelRow)
        {
            $args = $this->getLabelArguments($objModelRow);

            // Shorten the label if it is too long
            $label = vsprintf((strlen($this->dca['list']['label']['format']) ? $this->dca['list']['label']['format'] : '%s'), $args);

            if ($this->dca['list']['label']['maxCharacters'] > 0 && $this->dca['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
            {
                $this->import('String');
                $label = trim($this->String->substrHtml($label, $this->dca['list']['label']['maxCharacters'])) . ' …';
            }

            // Remove empty brackets (), [], {}, <> and empty tags from the label
            $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
            $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

            // Build the sorting groups
            if ($this->dca['list']['sorting']['mode'] > 0)
            {

                $current     = $objModelRow->getProperty($this->dc->getFirstSorting());
                $orderBy     = $this->dca['list']['sorting']['fields'];
                $sortingMode = (count($orderBy) == 1 && $this->dc->getFirstSorting() == $orderBy[0] && $this->dca['list']['sorting']['flag'] != '' && $this->dca['fields'][$this->dc->getFirstSorting()]['flag'] == '') ? $this->dca['list']['sorting']['flag'] : $this->dca['fields'][$this->dc->getFirstSorting()]['flag'];

                $remoteNew = $this->dc->formatCurrentValue($this->dc->getFirstSorting(), $current, $sortingMode);

                // Add the group header
                if (!$this->dca['list']['label']['showColumns'] && !$this->dca['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
                {
                    $eoCount = -1;

                    $objModelRow->setProperty('%group%', array(
                        'class' => $groupclass,
                        'value' => $this->dc->formatGroupHeader($this->dc->getFirstSorting(), $remoteNew, $sortingMode, $objModelRow)
                    ));

                    $groupclass = 'tl_folder_list';
                    $remoteCur  = $remoteNew;
                }
            }

            $objModelRow->setProperty('%rowClass%', ((++$eoCount % 2 == 0) ? 'even' : 'odd'));

            $colspan = 1;

            // Call label callback
            $mixedArgs = $this->dc->labelCallback($objModelRow, $label, $this->arrDCA['list']['label'], $args);

            if (is_array($this->arrDCA['list']['label']['label_callback']))
            {
                // Handle strings and arrays (backwards compatibility)
                if (!$this->dca['list']['label']['showColumns'])
                {
                    $label = is_array($mixedArgs) ? implode(' ', $mixedArgs) : $mixedArgs;
                }
                elseif (!is_array($mixedArgs))
                {
                    $mixedArgs = array($mixedArgs);
                    $colspan = count($this->dca['list']['label']['fields']);
                }
            }

            $arrLabel = array();

            // Add columns
            if ($this->dca['list']['label']['showColumns'])
            {
                foreach ($args as $j => $arg)
                {
                    $arrLabel[] = array(
                        'colspan' => $colspan,
                        'class' => 'tl_file_list col_' . $this->dca['list']['label']['fields'][$j] . (($this->dca['list']['label']['fields'][$j] == $this->dc->getFirstSorting()) ? ' ordered_by' : ''),
                        'content' => (($arg != '') ? $arg : '-')
                    );
                }
            }
            else
            {
                $arrLabel[] = array(
                    'colspan' => NULL,
                    'class' => 'tl_file_list',
                    'content' => $label
                );
            }

            $objModelRow->setProperty('%label%', $arrLabel);
        }
    }

    /**
     * Get arguments for label
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @return array
     */
    protected function getLabelArguments($objModelRow)
    {
        if ($this->dca['list']['sorting']['mode'] == 6)
        {
            $this->loadDataContainer($objDC->getParentTable());
            $objTmpDC = new DC_General($objDC->getParentTable());

            $arrCurrentDCA = $objTmpDC->getDCA();
        }
        else
        {
            $arrCurrentDCA = $this->dca;
        }

        $args = array();
        $showFields = $arrCurrentDCA['list']['label']['fields'];

        // Label
        foreach ($showFields as $k => $v)
        {
            if (strpos($v, ':') !== false)
            {
                $args[$k] = $objModelRow->getProperty('%args%');
            }
            elseif (in_array($this->dca['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
            {
                if ($this->dca['fields'][$v]['eval']['rgxp'] == 'date')
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objModelRow->getProperty($v));
                }
                elseif ($this->dca['fields'][$v]['eval']['rgxp'] == 'time')
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objModelRow->getProperty($v));
                }
                else
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objModelRow->getProperty($v));
                }
            }
            elseif ($this->dca['fields'][$v]['inputType'] == 'checkbox' && !$this->dca['fields'][$v]['eval']['multiple'])
            {
                $args[$k] = strlen($objModelRow->getProperty($v)) ? $arrCurrentDCA['fields'][$v]['label'][0] : '';
            }
            else
            {
                $row = deserialize($objModelRow->getProperty($v));

                if (is_array($row))
                {
                    $args_k = array();

                    foreach ($row as $option)
                    {
                        $args_k[] = strlen($arrCurrentDCA['fields'][$v]['reference'][$option]) ? $arrCurrentDCA['fields'][$v]['reference'][$option] : $option;
                    }

                    $args[$k] = implode(', ', $args_k);
                }
                elseif (isset($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]))
                {
                    $args[$k] = is_array($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]) ? $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)][0] : $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)];
                }
                elseif (($arrCurrentDCA['fields'][$v]['eval']['isAssociative'] || array_is_assoc($arrCurrentDCA['fields'][$v]['options'])) && isset($arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)]))
                {
                    $args[$k] = $arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)];
                }
                else
                {
                    $args[$k] = $objModelRow->getProperty($v);
                }
            }
        }

        return $args;
    }

    // Buttons -----------------------------------------------------------------

    /**
     * Generate header display buttons
     * 
     * @param string $strButtonId
     * @return string 
     */
    public function displayButtons($strButtonId)
    {
        $arrReturn = array();

        // Add open wrapper
        $arrReturn[] = '<div id="' . $strButtonId . '">';

        // Add back button
        $arrReturn[] = (($this->blnSelect || $this->dc->getParentTable()) ? '<a href="' . $this->getReferer(true, $this->dc->getParentTable()) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' : '');

        // Add divider
        $arrReturn[] = (($this->dc->getParentTable() && !$this->blnSelect) ? ' &nbsp; :: &nbsp;' : '');

        if (!$this->blnSelect)
        {
            // Add new button
            $arrReturn[] = ' ' . (!$this->dca['config']['closed'] ? '<a href="' . (strlen($this->dc->getParentTable()) ? $this->addToUrl('act=create' . (($this->dca['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->dc->getId()) : $this->addToUrl('act=create')) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->dc->getTable()]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG'][$this->dc->getTable()]['new'][0] . '</a>' : '');

            // Add global buttons
            $arrReturn[] = $this->generateGlobalButtons();
        }

        // Add close wrapper
        $arrReturn[] = '</div>';

        $arrReturn[] = $this->getMessages(true);

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
            $label      = strlen($v['label'][0]) ? $v['label'][0] : $k;
            $title      = sprintf((strlen($v['label'][1]) ? $v['label'][1] : $k), $objModelRow->getProperty('id'));
            $attributes = strlen($v['attributes']) ? ' ' . ltrim(sprintf($v['attributes'], $objModelRow->getProperty('id'), $objModelRow->getProperty('id'))) : '';

            // Call a custom function instead of using the default button
            $strButtonCallback = $this->dc->buttonCallback($objModelRow, $v, $label, $title, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);
            if (!is_null($strButtonCallback))
            {
                $return .= $strButtonCallback;
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
                $href  = strlen($v['href']) ? $v['href'] : '&amp;act=move';

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
        if (!is_array($this->dca['list']['global_operations']))
        {
            return '';
        }

        $return = '';
        foreach ($this->dca['list']['global_operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label      = is_array($v['label']) ? $v['label'][0] : $v['label'];
            $title      = is_array($v['label']) ? $v['label'][1] : $v['label'];
            $attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

            if (!strlen($label))
            {
                $label = $k;
            }

            // Call a custom function instead of using the default button
            $strButtonCallback = $this->dc->globalButtonCallback($v, $label, $title, $attributes, $this->dc->getTable(), $this->dc->getRootIds());
            if (!is_null($strButtonCallback))
            {
                $return .= $strButtonCallback;
                continue;
            }

            $return .= ' &#160; :: &#160; <a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
        }

        return ($this->dca['config']['closed'] && !$blnForceSeparator) ? preg_replace('/^ &#160; :: &#160; /', '', $return) : $return;
    }

}

?>