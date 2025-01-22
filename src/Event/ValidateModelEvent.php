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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event is emitted when a submitted and updated model has to be validated.
 *
 * This is triggered after the model has been updated but before rendering the edit mask and pre-persist.
 */
class ValidateModelEvent extends AbstractModelAwareEvent
{
    /** @psalm-suppress MissingClassConstType */
    public const NAME = 'dc-general.model.validate';

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface      $environment      The environment.
     * @param ModelInterface            $model            The model being validated.
     * @param PropertyValueBagInterface $propertyValueBag The property values.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        private readonly PropertyValueBagInterface $propertyValueBag
    ) {
        parent::__construct($environment, $model);
    }

    public function getPropertyValueBag(): PropertyValueBagInterface
    {
        return $this->propertyValueBag;
    }
}
