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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use Contao\Config;
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
    private $intOffset;

    /**
     * The current amount.
     *
     * @var int
     */
    private $intAmount;

    /**
     * The total amount of all valid entries.
     *
     * @var int
     */
    private $intTotal;

    /**
     * Retrieve the amount of items to display per page.
     *
     * @return int
     */
    protected function getItemsPerPage()
    {
        return Config::get('resultsPerPage');
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

        if (\is_array($total)) {
            $this->intTotal = $total ? \count($total) : 0;
        } elseif (\is_object($total)) {
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
        $arrValue = [];
        if ($this->getSessionStorage()->has('limit')) {
            $arrValue = $this->getSessionStorage()->get('limit');
        }

        if (\array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue)) {
            return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return [];
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param int $intOffset The offset.
     * @param int $intAmount The amount of items to show.
     *
     * @return void
     */
    protected function setPersistent($intOffset, $intAmount)
    {
        $arrValue       = [];
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('limit')) {
            $arrValue = $this->getSessionStorage()->get('limit');
        }

        if ($intOffset) {
            if (!\is_array($arrValue[$definitionName])) {
                $arrValue[$definitionName] = [];
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
            if ($input->hasValue('tl_limit') && $this->getPanel()->getContainer()->updateValues()) {
                $limit  = \explode(',', $input->getValue('tl_limit'));
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

                if ('all' === $offset) {
                    $offset = 0;
                    $amount = $this->intTotal;
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
        $arrOptions = [
            [
                'value'      => 'tl_limit',
                'attributes' => '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterRecords']
            ]
        ];

        $optionsPerPage = $this->getItemsPerPage();
        $optionsTotal   = \ceil($this->intTotal / $optionsPerPage);

        for ($i = 0; $i < $optionsTotal; $i++) {
            $first      = ($i * $optionsPerPage);
            $thisLimit  = $first . ',' . $optionsPerPage;
            $upperLimit = ($first + $optionsPerPage);

            if ($upperLimit > $this->intTotal) {
                $upperLimit = $this->intTotal;
            }

            $arrOptions[] = [
                'value'      => $thisLimit,
                'attributes' => ($this->getOffset() == $first) ? ' selected' : '',
                'content'    => ($first + 1) . ' - ' . $upperLimit
            ];
        }

        if ($this->intTotal > $optionsPerPage) {
            $arrOptions[] = [
                'value'      => 'all',
                'attributes' =>
                    (($this->getOffset() == 0) && ($this->getAmount() == $this->intTotal))
                        ? 'selected'
                        : '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterAll']
            ];
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
