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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * Page tree widget being compatible with the dc general.
 *
 * @see https://github.com/contao/core/blob/master/system/modules/core/widgets/PageTree.php
 *
 * @psalm-suppress PropertyNotSetInConstructor
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
     * @param mixed $varInput The input value.
     *
     * @return null|string|list<string>
     *
     */
    protected function validator($varInput)
    {
        $translator = $this->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $widgetValue = $this->widgetToValue($varInput);
        if ((null === $widgetValue) && $this->mandatory) {
            $this->addError($translator->translate('mandatory', 'ERR', [$this->strLabel]));
        }

        return $widgetValue;
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * @param mixed $rootId       The root element (or null to fetch everything).
     * @param int   $level        The current level in the tree (of the optional root element).
     * @param null  $providerName The data provider from which the optional root element shall be taken from.
     *
     * @return CollectionInterface
     */
    public function loadCollection($rootId = null, $level = 0, $providerName = null)
    {
        $collection = $this->getTreeCollectionRecursive($rootId, $level, $providerName);

        $dataProvider = $this->getEnvironment()->getDataProvider($providerName);
        assert($dataProvider instanceof DataProviderInterface);

        $treeData = $dataProvider->getEmptyCollection();
        if ($rootId) {
            $objModel = $collection->get(0);
            assert($objModel instanceof ModelInterface);

            foreach ($objModel->getMeta($objModel::CHILD_COLLECTIONS) as $childCollection) {
                foreach ($childCollection as $subModel) {
                    $treeData->push($subModel);
                }
            }
            return $treeData;
        }

        foreach ($collection as $model) {
            if ('root' !== $model->getProperty('type')) {
                continue;
            }

            $treeData->push($model);
        }

        return $treeData;
    }

    /**
     * Generate the picker url.
     *
     * @return string
     */
    protected function generatePickerUrl()
    {
        $extra = [
            'fieldType' => $this->fieldType
        ];

        $pickerBuilder = System::getContainer()->get('contao.picker.builder');
        assert($pickerBuilder instanceof PickerBuilderInterface);

        return $pickerBuilder->getUrl('page', $extra);
    }
}
