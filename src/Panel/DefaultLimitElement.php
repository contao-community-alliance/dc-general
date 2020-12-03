<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2020 Contao Community Alliance.
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
        return (int) Config::get('resultsPerPage');
    }

    /**
     * Retrieve the amount of max items to display per page.
     *
     * @return int
     */
    protected function getMaxItemsPerPage()
    {
        return (int) Config::get('maxResultsPerPage');
    }

    /**
     * Calculate the total amount of items.
     *
     * @return void
     */
    protected function calculateTotal()
    {
        $otherConfig = $this->getOtherConfig();
        $total       = $this
            ->getEnvironment()
            ->getDataProvider()
            ->fetchAll($otherConfig->setIdOnly(true));

        if (\is_array($total)) {
            $this->intTotal = $total ? \count($total) : 0;

            return;
        }

        if (\is_object($total)) {
            $this->intTotal = $total->length();

            return;
        }

        $this->intTotal = 0;
    }

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $values = [];
        if ($this->getSessionStorage()->has('limit')) {
            $values = $this->getSessionStorage()->get('limit');
        }

        if (\array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $values)) {
            return $values[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return [];
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param int $offset The offset.
     * @param int $amount The amount of items to show.
     *
     * @return void
     */
    protected function setPersistent($offset, $amount)
    {
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        $values = [];

        if ($this->getSessionStorage()->has('limit')) {
            $values = $this->getSessionStorage()->get('limit');
        }

        if ($offset) {
            if (!\is_array($values[$definitionName])) {
                $values[$definitionName] = [];
            }

            $values[$definitionName]['offset'] = $offset;
            $values[$definitionName]['amount'] = $amount;
        } else {
            unset($values[$definitionName]);
        }

        $this->getSessionStorage()->set('limit', $values);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null)
    {
        if (null === $element) {
            $this->calculateTotal();

            $offset = 0;
            $amount = $this->getItemsPerPage();

            if ('1' !== $this->getEnvironment()->getInputProvider()->getValue('filter_reset')) {

                $input = $this->getInputProvider();
                if ($input->hasValue('tl_limit') && $this->getPanel()->getContainer()->updateValues()) {
                    $limit = $input->getValue('tl_limit');
                    if ('tl_limit' !== $limit) {
                        [$offset, $amount] = \explode(',', $input->getValue('tl_limit'));
                        $this->setPersistent($offset, $amount);
                    }
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
                        $amount = $this->getAmountForFilterOptionAll();
                    }
                }

            } else {
              $this->setPersistent(null, null);
            }

            if (null !== $offset) {
                $this->setOffset($offset);
                $this->setAmount($amount);
            }
        }

        $config->setStart($this->getOffset());
        $config->setAmount($this->getAmount());
    }

    /**
     * Get the amount for the filter option all.
     *
     * @return int
     */
    private function getAmountForFilterOptionAll()
    {
        return $this->intTotal > $this->getMaxItemsPerPage() ? $this->getMaxItemsPerPage() : $this->intTotal;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $options = [
            [
                'value'      => '0,' . $this->getItemsPerPage(),
                'attributes' => '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterRecords']
            ]
        ];

        switch ($this->getInputProvider()->getValue('tl_limit')) {
            case 'all':
                $optionsPerPage = ($this->intTotal >= $this->getMaxItemsPerPage())
                    ? $this->getMaxItemsPerPage() : $this->getItemsPerPage();
                break;

            default:
                $optionsPerPage = $this->getItemsPerPage();
        }
        $optionsTotal = \ceil($this->intTotal / $optionsPerPage);

        for ($i = 0; $i < $optionsTotal; $i++) {
            $first      = ($i * $optionsPerPage);
            $thisLimit  = $first . ',' . $optionsPerPage;
            $upperLimit = ($first + $optionsPerPage);

            if ($upperLimit > $this->intTotal) {
                $upperLimit = $this->intTotal;
            }

            $options[] = [
                'value'      => $thisLimit,
                'attributes' => ($first === $this->getOffset()) ? ' selected' : '',
                'content'    => ($first + 1) . ' - ' . $upperLimit
            ];
        }

        if ($this->intTotal > $optionsPerPage) {
            $options[] = [
                'value'      => 'all',
                'attributes' =>
                    ((0 === $this->getOffset()) && ($this->intTotal === $this->getAmount()))
                        ? 'selected'
                        : '',
                'content'    => $GLOBALS['TL_LANG']['MSC']['filterAll']
            ];
        }

        $viewTemplate->set('options', $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOffset($offset)
    {
        $this->intOffset = (int) $offset;

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
