<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\BackendHelp;

final readonly class BackendHelpProvider implements BackendHelpProviderInterface
{
    /**
     * @param array<string, array<string, string>> $helpText
     */
    public function __construct(
        private array $helpText,
    ) {
    }

    #[\Override]
    public function getHelpFor(string $table, string $property): iterable
    {
        foreach ($this->helpText as $section => $helpTexts) {
            foreach ($helpTexts as $caption => $description) {
                yield new HelpText($section, $caption, $caption, $description);
            }
        }
    }
}
