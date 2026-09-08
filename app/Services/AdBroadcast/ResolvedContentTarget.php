<?php

namespace App\Services\AdBroadcast;

final readonly class ResolvedContentTarget
{
    public function __construct(
        public string $contentTarget,
        public ?string $categoryId = null,
        public ?string $subcategoryId = null,
        public ?string $serviceId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastAttributes(): array
    {
        return [
            'content_target' => $this->contentTarget,
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subcategoryId,
            'service_id' => $this->serviceId,
        ];
    }
}
