<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Picker;

use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;

/**
 * Provides the tree picker.
 */
class TreePickerProvider extends AbstractAwarePickerProvider implements DcaPickerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ccaTreePicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return 'cca_tree' === $context;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config): bool
    {
        if ('cca_tree' === $config->getContext()) {
            return is_numeric($config->getValue());
        }

        return false !== strpos($config->getValue(), '{{link_url::');
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable(): string
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

        if ('cca_tree' === $config->getContext()) {
            if ($fieldType = $config->getExtra('fieldType')) {
                $attributes['fieldType'] = $fieldType;
            }

            if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($value) {
                $attributes['value'] = \array_map('intval', \explode(',', $value));
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
    public function convertDcaValue(PickerConfig $config, $value): string|int
    {
        if ('cca_tree' === $config->getContext()) {
            return (int) $value;
        }

        return '{{link_url::' . $value . '}}';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'tree'];
    }
}
