<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\BackendHelp;

final readonly class HelpText
{
    public function __construct(
        private string $section,
        private string $caption,
        private string $description,
        private ?string $translationDomain = null,
    ) {
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }
}
