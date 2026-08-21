<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use Contao\Config;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * Default implementation of a limit panel element.
 *
 * @api
 */
class DefaultLimitElement extends AbstractElement implements LimitElementInterface, TotalAwareLimitElementInterface
{
    /**
     * The url parameter a pagination uses to request a page.
     *
     * @var string
     */
    public const string PAGE_PARAMETER = 'lp';

    /**
     * The current offset.
     *
     * @var int
     */
    private int $intOffset = 0;

    /**
     * The current amount.
     *
     * @var int
     */
    private int $intAmount = 0;

    /**
     * The total amount of all valid entries.
     *
     * @var int
     */
    private int $intTotal = 0;

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
        assert($otherConfig instanceof ConfigInterface);

        $dataProvider = $this->getEnvironment()->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $total = $dataProvider->fetchAll($this->buildTotalConfig($otherConfig));

        if (\is_array($total)) {
            $this->intTotal = $total ? \count($total) : 0;

            return;
        }

        $this->intTotal = $total->length();
    }

    /**
     * Build the config the total is counted with.
     *
     * In a hierarchical view only the root nodes are limited: the children are rendered below their
     * parent and must not count towards the pages, otherwise the amount of pages would depend on how
     * many variants happen to hang below a base. The config is copied because the one handed in is
     * kept for the lifetime of the element.
     *
     * @param ConfigInterface $otherConfig The config carrying the filters of the other panel elements.
     *
     * @return ConfigInterface
     */
    private function buildTotalConfig(ConfigInterface $otherConfig): ConfigInterface
    {
        $config = clone $otherConfig;
        $config->setIdOnly(true);

        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $rootCondition =
            BasicDefinitionInterface::MODE_HIERARCHICAL === $definition->getBasicDefinition()->getMode()
                ? $definition->getModelRelationshipDefinition()->getRootCondition()
                : null;

        if (null === $rootCondition) {
            return $config;
        }

        $base = $config->getFilter();

        return $config->setFilter(
            \is_array($base) ? \array_merge($base, $rootCondition->getFilterArray()) : $rootCondition->getFilterArray()
        );
    }

    /**
     * {@inheritDoc}
     *
     * The value is filled while the element determines its options. Asking before that happened
     * yields zero rather than a stale number.
     */
    #[\Override]
    public function getTotal(): int
    {
        return $this->intTotal;
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
            $values = (array) $this->getSessionStorage()->get('limit');
        }

        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (\array_key_exists($definition->getName(), $values)) {
            return (array) $values[$definition->getName()];
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
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $definitionName = $definition->getName();

        $values = [];

        if ($this->getSessionStorage()->has('limit')) {
            $values = (array) $this->getSessionStorage()->get('limit');
        }

        if (!$offset && !$amount) {
            // Filter reset.
            unset($values[$definitionName]);
        } else {
            if (!isset($values[$definitionName]) || !\is_array($values[$definitionName])) {
                $values[$definitionName] = [];
            }

            $values[$definitionName]['offset'] = $offset;
            $values[$definitionName]['amount'] = $amount;
        }

        $this->getSessionStorage()->set('limit', $values);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function initialize(ConfigInterface $config, ?PanelElementInterface $element = null)
    {
        if (null !== $element) {
            $config->setStart($this->getOffset());
            $config->setAmount($this->getAmount());

            return;
        }

        $this->calculateTotal();

        $offset = 0;
        $amount = $this->getItemsPerPage();

        $this->defineOffsetAndAmountOption($offset, $amount);

        $this->setOffset($offset);
        $this->setAmount($amount);
        $config->setStart($offset);
        $config->setAmount($amount);
    }

    /**
     * Define amount and offset option for the filter.
     *
     * @param int $offset The offset for the filter option.
     * @param int $amount The amount for the filter option.
     *
     * @return void
     */
    private function defineOffsetAndAmountOption(int &$offset, int &$amount): void
    {
        $input = $this->getInputProvider();
        if ('1' === $input->getValue('filter_reset')) {
            $this->setPersistent(0, 0);

            return;
        }

        $panel = $this->getPanel();
        assert($panel instanceof PanelInterface);

        $panelSubmitted = $input->hasValue('tl_limit') && $panel->getContainer()->updateValues();

        if ($panelSubmitted) {
            $limit = (string) $input->getValue('tl_limit');
            if ('all' === $limit) {
                $offset = 0;
                $amount = $this->getAmountForFilterOptionAll();
                $this->setPersistent($offset, $amount);
                return;
            }
            [$offset, $amount] = \explode(',', $limit) + [0, 0];
            $offset = (int) $offset;
            $amount = (int) $amount;
            $this->setPersistent($offset, $amount);
        }

        $persistent = $this->getPersistent();
        if ($persistent) {
            $offset = (int) $persistent['offset'];
            $amount = (int) $persistent['amount'];

            // A stored offset that no longer fits the current total - most commonly because a
            // filter was changed or tightened since - would otherwise query a page that does not
            // exist and render empty. ">=" rather than ">": an offset equal to the total is already
            // one past the last valid row, the same as being beyond it.
            if ($offset >= $this->intTotal) {
                $offset = 0;
            }
        }

        // A page picked from the pagination overrules the stored offset. Never when the panel was
        // submitted though: that carries an offset of its own, and a stale page parameter left in the
        // url would otherwise undo a filter change by jumping back to where the user came from.
        $page = (int) $input->getParameter(self::PAGE_PARAMETER);
        if (!$panelSubmitted && $page > 0) {
            $offset = $this->offsetForPage($page, $amount);
            $this->setPersistent($offset, $amount);
        }
    }

    /**
     * Determine the offset a page starts at, clamped to the range the listing actually has.
     *
     * @param int $page   The page, one based.
     * @param int $amount The amount of records per page.
     *
     * @return int
     */
    private function offsetForPage(int $page, int $amount): int
    {
        if ($amount < 1) {
            return 0;
        }

        $lastPage = \max(1, (int) \ceil($this->intTotal / $amount));

        return (\min($lastPage, $page) - 1) * $amount;
    }

    /**
     * Get the amount for the filter option all.
     *
     * @return int
     */
    private function getAmountForFilterOptionAll(): int
    {
        return $this->intTotal > $this->getMaxItemsPerPage() ? $this->getMaxItemsPerPage() : $this->intTotal;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    #[\Override]
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $translator = $this->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $options = [
            [
                'value'      => '0,' . $this->getItemsPerPage(),
                'attributes' => '',
                'content'    => $translator->translate('filterRecords', 'dc-general')
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
                'content'    => $translator->translate('filterAll', 'dc-general')
            ];
        }

        $viewTemplate->set('options', $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setOffset($intOffset)
    {
        $this->intOffset = $intOffset;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getOffset()
    {
        return $this->intOffset;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setAmount($intAmount)
    {
        $this->intAmount = $intAmount;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getAmount()
    {
        return $this->intAmount;
    }
}
