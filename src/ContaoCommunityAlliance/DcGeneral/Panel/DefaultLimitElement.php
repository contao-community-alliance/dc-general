<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a limit panel element.
 */
class DefaultLimitElement extends AbstractElement implements LimitElementInterface
{
    /**
     * The current offset.
     *
     * @var int
     */
    protected $intOffset;

    /**
     * The current amount.
     *
     * @var int
     */
    protected $intAmount;

    /**
     * The total amount of all valid entries.
     *
     * @var int
     */
    protected $intTotal;

    /**
     * Retrieve the amount of items to display per page.
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getItemsPerPage()
    {
        if (version_compare(VERSION, '3', '<')) {
            return $GLOBALS['TL_CONFIG']['resultsPerPage'];
        }
        return \Config::get('resultsPerPage');
    }

    /**
     * Calculate the total amount of items.
     *
     * @return void
     */
    protected function calculateTotal()
    {
        $objTempConfig = $this->getOtherConfig();
        $total         = $this
            ->getEnvironment()
            ->getDataProvider()
            ->fetchAll($objTempConfig->setIdOnly(true));

        if (is_array($total)) {
            $this->intTotal = $total ? count($total) : 0;
        } elseif (is_object($total)) {
            $this->intTotal = $total->length();
        } else {
            $this->intTotal = 0;
        }
    }

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $arrValue = array();
        if ($this->getSessionStorage()->has('limit')) {
            $arrValue = $this->getSessionStorage()->get('limit');
        }

        if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue)) {
            return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return array();
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param int $intOffset The offset.
     *
     * @param int $intAmount The amount of items to show.
     *
     * @return void
     */
    protected function setPersistent($intOffset, $intAmount)
    {
        $arrValue       = array();
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('limit')) {
            $arrValue = $this->getSessionStorage()->get('limit');
        }

        if ($intOffset) {
            if (!is_array($arrValue[$definitionName])) {
                $arrValue[$definitionName] = array();
            }

            $arrValue[$definitionName]['offset'] = $intOffset;
            $arrValue[$definitionName]['amount'] = $intAmount;
        } else {
            unset($arrValue[$definitionName]);
        }

        $this->getSessionStorage()->set('limit', $arrValue);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
    {
        if ($objElement === null) {
            $this->calculateTotal();

            $offset = 0;
            $amount = $this->getItemsPerPage();

            $input = $this->getInputProvider();
            if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_limit')) {
                $limit  = explode(',', $input->getValue('tl_limit'));
                $offset = $limit[0];
                $amount = $limit[1];

                $this->setPersistent($offset, $amount);
            }

            $persistent = $this->getPersistent();
            if ($persistent) {
                $offset = $persistent['offset'];
                $amount = $persistent['amount'];

                // Hotfix the offset - we also might want to store it persistent.
                // Another way would be to always stick on the "last" page when we hit the upper limit.
                if ($offset > $this->intTotal) {
                    $offset = 0;
                }
            }

            if ($offset !== null) {
                $this->setOffset($offset);
                $this->setAmount($amount);
            }
        }

        $objConfig->setStart($this->getOffset());
        $objConfig->setAmount($this->getAmount());
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function render(ViewTemplateInterface $objTemplate)
    {
        $arrOptions = array(
            array(
                'value'      => 'tl_limit',
                'attributes' => '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterRecords']
            )
        );

        $optionsTotal = ceil(($this->intTotal / $GLOBALS['TL_CONFIG']['resultsPerPage']));

        for ($i = 0; $i < $optionsTotal; $i++) {
            $first      = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']);
            $thisLimit  = $first . ',' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
            $upperLimit = ($first + $GLOBALS['TL_CONFIG']['resultsPerPage']);

            if ($upperLimit > $this->intTotal) {
                $upperLimit = $this->intTotal;
            }

            $arrOptions[] = array(
                'value'      => $thisLimit,
                'attributes' => ($this->getOffset() == $first) ? ' selected="selected"' : '',
                'content'    => ($first + 1) . ' - ' . $upperLimit
            );
        }

        if ($this->intTotal > $GLOBALS['TL_CONFIG']['resultsPerPage']) {
            $arrOptions[] = array(
                'value'      => 'all',
                'attributes' =>
                    (($this->getOffset() == 0) && ($this->getAmount() == $this->intTotal))
                        ? ' selected="selected"'
                        : '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterAll']
            );
        }

        $objTemplate->set('options', $arrOptions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOffset($intOffset)
    {
        $this->intOffset = $intOffset;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOffset()
    {
        return $this->intOffset;
    }

    /**
     * {@inheritDoc}
     */
    public function setAmount($intAmount)
    {
        $this->intAmount = $intAmount;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAmount()
    {
        return $this->intAmount;
    }
}
