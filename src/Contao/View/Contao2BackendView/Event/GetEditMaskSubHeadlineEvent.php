<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class GetEditModeButtonsEvent.
 *
 * This event is triggered when the sub-headline is generated.
 */
class GetEditMaskSubHeadlineEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The name of the event.
     */
    public const NAME = 'dc-general.view.contao2backend.get-edit-mask-subheadline';

    /**
     * The model attached to the event.
     *
     * @var ModelInterface
     */
    private $model;

    /**
     * The sub-headline.
     *
     * @var string|null
     */
    private $subHeadline;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param ModelInterface       $model       The model attached to the event.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        parent::__construct($environment);
        $this->model = $model;
    }

    /**
     * Retrieve the attached model.
     *
     * @return ModelInterface
     */
    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    /**
     * Set the sub-headline.
     *
     * @param string $subHeadline The sub-headline to be returned.
     *
     * @return $this
     */
    public function setHeadline(string $subHeadline): self
    {
        $this->subHeadline = $subHeadline;

        return $this;
    }

    /**
     * Get the sub-headline.
     *
     * @return string|null
     */
    public function getHeadline(): ?string
    {
        if (false === isset($this->subHeadline)) {
            return null;
        }

        return $this->subHeadline;
    }
}
