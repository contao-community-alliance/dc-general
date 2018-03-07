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
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;

/**
 * Page tree widget being compatible with the dc general.
 *
 * @see https://github.com/contao/core/blob/master/system/modules/core/widgets/PageTree.php
 */
class PageTree extends TreePicker
{
    /**
     * The sub template to use when generating.
     *
     * @var string
     */
    protected $subTemplate = 'widget_pagetree';

    /**
     * Process the validation.
     *
     * @param mixed $inputValue The input value.
     *
     * @return array|string
     */
    protected function validator($inputValue)
    {
        $translator = $this->getEnvironment()->getTranslator();

        if ('' === $inputValue) {
            if ($this->mandatory) {
                $this->addError($translator->translate('mandatory', 'ERR', [$this->strLabel]));
            }

            return '';
        }

        $inputValue = explode(',', $inputValue);

        return $this->multiple ? $inputValue : $inputValue[0];
    }
}
