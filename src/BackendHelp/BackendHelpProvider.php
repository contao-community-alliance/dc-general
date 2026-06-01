<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\BackendHelp;

final readonly class BackendHelpProvider implements BackendHelpProviderInterface
{
    public function __construct(
        private array $helpText,
    ) {
    }

    #[\Override]
    public function getHelpFor(string $table, string $property): iterable
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($this->helpText as $section => $helpTexts) {
            /** @psalm-suppress MixedAssignment */
            foreach ((array) $helpTexts as $caption => $description) {
                yield new HelpText((string) $section, (string) $caption, (string) $caption, (string) $description);
            }
        }
    }
}
