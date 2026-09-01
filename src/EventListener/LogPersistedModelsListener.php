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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\EventListener;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\LoggingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use Psr\Log\LoggerInterface;

/**
 * Mirrors what Contao's own DC_Table logs for its tables into the Contao system log (tl_log) for
 * DC_General-driven tables, which so far never logged anything - see ".claude/dcg-systemlog.md".
 *
 * Deliberately covers create, duplicate and delete only, matching what Contao itself logs for its
 * own tables. Two things that might look missing are excluded on purpose, not overlooked:
 *
 * - Editing an existing record. Contao does not log edits either - that is what versioning is for.
 * - Toggling a boolean field (publish state and the like). It fires the very same
 *   PostPersistModelEvent as a normal edit, with the same non-null original model, and is
 *   therefore structurally indistinguishable from it without a property-by-property diff Contao
 *   itself does not do either - so it stays out for the same reason a normal edit does.
 *
 * @api
 */
class LogPersistedModelsListener
{
    /**
     * Create a new instance.
     *
     * @param LoggerInterface $logger The "contao.general" channel logger.
     */
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * Log the creation of a new record.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function onPersist(PostPersistModelEvent $event): void
    {
        // Edits fire this same event with the previously stored data as original model. A create
        // is not signalled by a null original model - CreateHandler passes an empty one
        // (getEmptyModel()), never a literal null, regardless of what the event's own docblock
        // says - it has no id yet, which is what actually tells the two apart.
        $originalModel = $event->getOriginalModel();
        if (null !== $originalModel && null !== $originalModel->getId()) {
            return;
        }

        $model = $event->getModel();
        if (!$this->isLoggingEnabled($event->getEnvironment(), $model)) {
            return;
        }

        $this->logger->info(
            \sprintf('A new entry "%s.id=%s" has been created', $model->getProviderName(), (string) $model->getId())
        );
    }

    /**
     * Log the creation of a record by duplicating another one.
     *
     * @param PostDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function onDuplicate(PostDuplicateModelEvent $event): void
    {
        $model = $event->getModel();
        if (!$this->isLoggingEnabled($event->getEnvironment(), $model)) {
            return;
        }

        $source = $event->getSourceModel();
        $this->logger->info(
            \sprintf(
                'A new entry "%s.id=%s" has been created by duplicating record "%s.id=%s"',
                $model->getProviderName(),
                (string) $model->getId(),
                $source->getProviderName(),
                (string) $source->getId()
            )
        );
    }

    /**
     * Log the deletion of a record.
     *
     * @param PostDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function onDelete(PostDeleteModelEvent $event): void
    {
        $model = $event->getModel();
        if (!$this->isLoggingEnabled($event->getEnvironment(), $model)) {
            return;
        }

        $this->logger->info(
            \sprintf('DELETE FROM %s WHERE id=%s', $model->getProviderName(), (string) $model->getId())
        );
    }

    /**
     * Determine if logging is enabled for the data provider the given model belongs to.
     *
     * A provider information that does not implement LoggingInformationInterface at all - either
     * a foreign DataProviderInformationInterface implementation, or none registered yet - counts
     * as "not configured", which is treated the same as enabled.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelInterface       $model       The model.
     *
     * @return bool
     */
    private function isLoggingEnabled(EnvironmentInterface $environment, ModelInterface $model): bool
    {
        $definition = $environment->getDataDefinition();
        if (!$definition instanceof ContainerInterface) {
            return true;
        }

        $providerDefinition = $definition->getDataProviderDefinition();
        if (!$providerDefinition->hasInformation($model->getProviderName())) {
            return true;
        }

        $information = $providerDefinition->getInformation($model->getProviderName());
        if (!$information instanceof LoggingInformationInterface) {
            return true;
        }

        return $information->isLoggingEnabled();
    }
}
