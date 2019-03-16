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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Contao\Twig;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension for twig template engine.
 */
class DcGeneralExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dc-general';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [new TwigFilter('serializeModelId', [$this, 'serializeModelId'])];
    }

    /**
     * Serialize a model and return its ID.
     *
     * @param ModelInterface $model The model.
     *
     * @return string
     */
    public function serializeModelId(ModelInterface $model): string
    {
        return ModelId::fromModel($model)->getSerialized();
    }
}
