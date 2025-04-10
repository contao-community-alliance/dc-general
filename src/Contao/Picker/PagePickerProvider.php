<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Picker;

use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\System;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides the page picker.
 */
class PagePickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{
    private Security $security;

    /**
     * @internal
     */
    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        ?TranslatorInterface $translator,
        ?Security $security
    ) {
        if (null === $translator) {
            $translator = System::getContainer()->get('translator');
            assert($translator instanceof TranslatorInterface);

            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the translator as argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        parent::__construct($menuFactory, $router, $translator);

        if (null === $security) {
            $security = System::getContainer()->get('security.helper');
            assert($security instanceof Security);

            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the security as argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ccaPagePicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext(string $context): bool
    {
        return \in_array($context, ['cca_page', 'cca_link'], true)
               && $this->security->isGranted('contao_user.modules', 'page');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config): bool
    {
        if ('page' === $config->getContext()) {
            return \is_numeric($config->getValue());
        }

        return false !== \strpos($config->getValue(), '{{link_url::');
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable(PickerConfig $config = null): string
    {
        return 'tl_page';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config): array
    {
        $value      = $config->getValue();
        $attributes = ['fieldType' => 'radio'];

        if ('page' === $config->getContext()) {
            if ($fieldType = $config->getExtra('fieldType')) {
                $attributes['fieldType'] = $fieldType;
            }

            if ($source = $config->getExtra('source')) {
                $attributes['preserveRecord'] = $source;
            }

            if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($value) {
                $intval = static function (mixed $val): int {
                    return (int) $val;
                };

                $attributes['value'] = \array_map($intval, \explode(',', $value));
            }

            return $attributes;
        }

        if ($value && false !== \strpos($value, '{{link_url::')) {
            $attributes['value'] = \str_replace(['{{link_url::', '}}'], '', $value);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, mixed $value): int|string
    {
        if ('page' === $config->getContext()) {
            return (int) $value;
        }

        return '{{link_url::' . $value . '}}';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        return ['do' => 'page'];
    }
}
