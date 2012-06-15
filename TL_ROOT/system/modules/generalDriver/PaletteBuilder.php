<?php

class PaletteBuilder extends Controller
{

    protected $objDC;
    protected $arrSelectors = array();
    protected $arrAjaxPalettes = array();
    protected $arrRootPalette = array();
    protected $arrStack = array();

    /**
     * Current model
     * @var InterfaceGeneralModel 
     */
    protected $objCurrentModel = null;

    public function __construct(DC_General $objDC)
    {
        parent::__construct();

        $this->objDC = $objDC;
        $this->objCurrentModel = $this->objDC->getCurrentModel();
        $this->arrStack[] = $objDC->getSubpalettesDefinition();

        $this->calculateSelectors($this->arrStack[0]);
        $this->parseRootPalette();
    }

    public function isSelector($strSelector)
    {
        return isset($this->arrSelectors[$strSelector]);
    }

    public function getSelectors()
    {
        return $this->arrSelectors;
    }

    public function isEmpty()
    {
        return !count($this->arrRootPalette);
    }

    public function generateAjaxPalette($strSelector, $strInputName, $strFieldTemplate)
    {
        return is_array($this->arrAjaxPalettes[$strSelector]) ? sprintf('<div id="sub_%s">%s</div>', $strInputName, $this->generatePalette($this->arrAjaxPalettes[$strSelector], $strFieldTemplate)
                ) : '';
    }

    public function generateFieldsets($strFieldTemplate, array $arrStates)
    {
        $arrRootPalette = $this->arrRootPalette;
        
        foreach ($arrRootPalette as &$arrFieldset)
        {
            $strClass = 'tl_box';

            if ($strLegend = &$arrFieldset['legend'])
            {
                $arrClasses = explode(':', substr($strLegend, 1, -1));
                $strLegend  = array_shift($arrClasses);
                $arrClasses = array_flip($arrClasses);
                if (isset($arrStates[$strLegend]))
                {
                    if ($arrStates[$strLegend])
                    {
                        unset($arrClasses['hide']);
                    }
                    else
                    {
                        $arrClasses['collapsed'] = true;
                    }
                }
                $strClass .= ' ' . implode(' ', array_keys($arrClasses));
                $arrFieldset['label']    = isset($GLOBALS['TL_LANG'][$this->objDC->getTable()][$strLegend]) ? $GLOBALS['TL_LANG'][$this->objDC->getTable()][$strLegend] : $strLegend;
            }

            $arrFieldset['class']   = $strClass;
            $arrFieldset['palette'] = $this->generatePalette($arrFieldset['palette'], $strFieldTemplate);
        }
        
        return $arrRootPalette;
    }

//    public function help()
//    {
//       
//        return $this->objDC->help();
//    }

    protected function generatePalette(array $arrPalette, $strFieldTemplate)
    {
        ob_start();
        
        foreach ($arrPalette as $varField)
        {            
            if (is_array($varField))
            {
                /* $strName => this is the input name from the last loop */ 
                echo '<div id="sub_' . $strName . '">', $this->generatePalette($varField, $strFieldTemplate), '</div>';
            }
            else
            {
                $objWidget = $this->objDC->getWidget($varField);
                
                if (!$objWidget instanceof Widget)
                {
                    echo $objWidget;
                    continue;
                }

                $arrConfig = $this->objDC->getFieldDefinition($varField);

                $strClass = $arrConfig['eval']['tl_class'];

                // this should be correctly specified in DCAs
//				if($arrConfig['inputType'] == 'checkbox'
//				&& !$arrConfig['eval']['multiple']
//				&& strpos($strClass, 'w50') !== false
//				&& strpos($strClass, 'cbx') === false)
//					$strClass .= ' cbx';

                if ($arrConfig['eval']['submitOnChange'] && $this->isSelector($varField))
                {
                    $objWidget->onclick = '';
                    $objWidget->onchange = '';
                    $strClass .= ' selector';
                }

                $strName       = specialchars($objWidget->name);
                $blnUpdate     = $arrConfig['update'];
                $strDatepicker = '';
                if ($arrConfig['eval']['datepicker'])
                {
                    if (version_compare(VERSION, '2.10', '>='))
                    {
                        $strDatepicker = $this->buildPagePicker($objWidget);
                    }
                    else
                    {
                        $strDatepicker = sprintf($arrConfig['eval']['datepicker'], json_encode('ctrl_' . $objWidget->id));
                    }
                }
                                
                $objTemplateFoo = new BackendTemplate($strFieldTemplate);
                $objTemplateFoo->strName = $strName;
                $objTemplateFoo->strClass = $strClass;
                $objTemplateFoo->objWidget = $objWidget;
                $objTemplateFoo->strDatepicker = $strDatepicker;
                $objTemplateFoo->blnUpdate = $blnUpdate;
                $objTemplateFoo->strHelp = $this->objDC->generateHelpText($varField);

                echo $objTemplateFoo->parse();

                if (strncmp($arrConfig['eval']['rte'], 'tiny', 4) === 0 && (version_compare(VERSION, '2.10', '>=') || $this->Input->post('isAjax')))
                {
                    echo '<script>tinyMCE.execCommand("mceAddControl", false, "ctrl_' . $strName . '");</script>';
                }
            }
        }
        
        return ob_get_clean();
    }

    protected function calculateSelectors(array $arrSubpalettes = null)
    {
        if (!$arrSubpalettes)
            return;

        foreach ($arrSubpalettes as $strField => $varSubpalette)
        {
            $this->arrSelectors[$strField] = $this->objDC->isEditableField($strField);
            if (!is_array($varSubpalette))
                continue;

            foreach ($varSubpalette as $arrNested)
                if (is_array($arrNested))
                    $this->calculateSelectors($arrNested['subpalettes']);
        }
    }

    protected function parseRootPalette()
    {
        foreach (trimsplit(';', $this->selectRootPalette()) as $strPalette)
        {
            if ($strPalette[0] == '{')
                list($strLegend, $strPalette) = explode(',', $strPalette, 2);

            $arrPalette = $this->parsePalette($strPalette, array());
            
            if ($arrPalette)
            {
                $this->arrRootPalette[] = array(
                    'legend' => $strLegend,
                    'palette' => $arrPalette
                );
            }
        }
    }

    protected function parsePalette($strPalette, array $arrPalette)
    {
        if (!$strPalette)
            return $arrPalette;

        foreach (trimsplit(',', $strPalette) as $strField)
        {
            if (!$strField)
                continue;

            $varValue      = $this->objCurrentModel->getProperty($strField);
            $varSubpalette = $this->getSubpalette($strField, $varValue);
            
            if (is_array($varSubpalette))
            {
                $arrSubpalettes = $varSubpalette['subpalettes'];
                $varSubpalette  = $varSubpalette['palette'];
            }

            array_push($this->arrStack, is_array($arrSubpalettes) ? $arrSubpalettes : array());
            
            if ($this->objDC->isEditableField($strField))
            {
                $arrPalette[]  = $strField;
                $arrSubpalette = $this->parsePalette($varSubpalette, array());
                if ($arrSubpalette)
                {
                    $arrPalette[] = $arrSubpalette;
                    if ($this->isSelector($strField))
                        $this->arrAjaxPalettes[$strField] = $arrSubpalette;
                }
            } else
            { // selector field not editable, inline editable fields of active subpalette
                $arrPalette = $this->parsePalette($varSubpalette, $arrPalette);
            }

            array_pop($this->arrStack);
        }

        return $arrPalette;
    }

    protected function getSubpalette($strField, $varValue)
    {
        if ($this->arrAjaxPalettes[$strField])
        {
            throw new Exception("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
        }

        for ($i = count($this->arrStack) - 1; $i > -1; $i--)
        {
            if (isset($this->arrStack[$i][$strField]))
            {
                if (is_array($this->arrStack[$i][$strField]))
                {
                    return $this->arrStack[$i][$strField][$varValue];
                }
                else
                { // old style
                    return $varValue ? $this->arrStack[$i][$strField] : null;
                }
            }
            elseif (isset($this->arrStack[$i][$strField . '_' . $varValue]))
            {
                return $this->arrStack[$i][$strField . '_' . $varValue];
            }
        }
    }

    protected function selectRootPalette()
    {
        $arrPalettes  = $this->objDC->getPalettesDefinition();
        $arrSelectors = $arrPalettes['__selector__'];

        if (!$arrSelectors)
            return $arrPalettes['default'];

        $arrKeys = array();
        foreach ($arrSelectors as $strSelector)
        {
            $varValue = $this->objCurrentModel->getProperty($strSelector);

            if (!strlen($varValue))
                continue;

            $arrDef    = $this->objDC->getFieldDefinition($strSelector);
            $arrKeys[] = $arrDef['inputType'] == 'checkbox' && !$arrDef['eval']['multiple'] ? $strSelector : $varValue;
        }

        // Build possible palette names from the selector values
        if (!$arrKeys)
            return $arrPalettes['default'];

        // Get an existing palette
        foreach (self::combiner($arrKeys) as $strKey)
            if (is_string($arrPalettes[$strKey]))
                return $arrPalettes[$strKey];
            
        var_dump($arrPalettes);
        exiT();

        return $arrPalettes['default'];
    }

    protected function buildPagePicker($objWidget)
    {
        $strFormat = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

        $arrConfig = array(
            'allowEmpty' => true,
            'toggleElements' => '#toggle_' . $objWidget->id,
            'pickerClass' => 'datepicker_dashboard',
            'format' => $strFormat,
            'inputOutputFormat' => $strFormat,
            'positionOffset' => array('x' => 130, 'y' => -185),
            'startDay' => $GLOBALS['TL_LANG']['MSC']['weekOffset'],
            'days' => array_values($GLOBALS['TL_LANG']['DAYS']),
            'dayShort' => $GLOBALS['TL_LANG']['MSC']['dayShortLength'],
            'months' => array_values($GLOBALS['TL_LANG']['MONTHS']),
            'monthShort' => $GLOBALS['TL_LANG']['MSC']['monthShortLength']
        );

        switch ($objWidget->rgxp)
        {
            case 'datim':
                $arrConfig['timePicker'] = true;
                break;

            case 'time':
                $arrConfig['timePickerOnly'] = true;
                break;
        }

        return 'new DatePicker(' . json_encode('#ctrl_' . $objWidget->id) . ', ' . json_encode($arrConfig) . ');';
    }

    public static function combiner($names)
    {
        $return = array('');

        for ($i = 0; $i < count($names); $i++)
        {
            $buffer = array();

            foreach ($return as $k => $v)
            {
                $buffer[] = ($k % 2 == 0) ? $v : $v . $names[$i];
                $buffer[] = ($k % 2 == 0) ? $v . $names[$i] : $v;
            }

            $return = $buffer;
        }

        return array_filter($return);
    }

}
