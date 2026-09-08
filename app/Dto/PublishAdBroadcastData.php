<?php

namespace App\Dto;

final readonly class PublishAdBroadcastData
{
    /**
     * @param  list<string>  $audiences
     * @param  list<string>|null  $zoneIds
     * @param  list<string>|null  $explicitUserIds
     */
    public function __construct(
        public array $audiences,
        public ?array $zoneIds,
        /** @var string|array<string, string> */
        public string|array $title,
        /** @var string|array<string, string>|null */
        public string|array|null $description,
        public ?string $storedImagePath,
        public ?string $resendStoragePath,
        public ?string $publisherUserId,
        public ?array $explicitUserIds = null,
        public string $contentMode = 'none',
        public ?string $parentCategoryId = null,
        public ?string $childCategoryId = null,
        public ?string $serviceId = null,
    ) {}

    public function imagePathForRecord(): ?string
    {
        return $this->storedImagePath ?? $this->resendStoragePath;
    }
}
