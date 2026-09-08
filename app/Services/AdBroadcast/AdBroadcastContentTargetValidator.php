<?php

namespace App\Services\AdBroadcast;

use App\Models\AdBroadcast;
use Illuminate\Validation\ValidationException;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class AdBroadcastContentTargetValidator
{
    public const CONTENT_MODE_NONE = 'none';

    public const CONTENT_MODE_CATEGORY = 'category';

    public const CONTENT_MODE_SERVICE = 'service';

    /**
     * @throws ValidationException
     */
    public function resolve(
        string $contentMode,
        ?string $parentCategoryId = null,
        ?string $childCategoryId = null,
        ?string $serviceId = null,
    ): ResolvedContentTarget {
        $contentMode = trim($contentMode);

        if (! in_array($contentMode, [
            self::CONTENT_MODE_NONE,
            self::CONTENT_MODE_CATEGORY,
            self::CONTENT_MODE_SERVICE,
        ], true)) {
            throw ValidationException::withMessages([
                'content_mode' => translate('Invalid content target'),
            ]);
        }

        if ($contentMode === self::CONTENT_MODE_NONE) {
            return new ResolvedContentTarget(AdBroadcast::CONTENT_TARGET_NONE);
        }

        if ($contentMode === self::CONTENT_MODE_CATEGORY) {
            return $this->resolveCategoryMode($parentCategoryId, $childCategoryId);
        }

        return $this->resolveServiceMode($serviceId);
    }

    /**
     * @throws ValidationException
     */
    protected function resolveCategoryMode(?string $parentCategoryId, ?string $childCategoryId): ResolvedContentTarget
    {
        if ($parentCategoryId === null || $parentCategoryId === '') {
            throw ValidationException::withMessages([
                'parent_category_id' => translate('Category is required'),
            ]);
        }

        $parent = Category::query()
            ->withoutGlobalScopes()
            ->whereKey($parentCategoryId)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('position', 1);
            })
            ->first();

        if (! $parent) {
            // Soft-validate: store the ID even if the row is missing/inactive.
            return new ResolvedContentTarget(
                AdBroadcast::CONTENT_TARGET_CATEGORY,
                categoryId: $parentCategoryId,
            );
        }

        if ($childCategoryId === null || $childCategoryId === '') {
            return new ResolvedContentTarget(
                AdBroadcast::CONTENT_TARGET_CATEGORY,
                categoryId: (string) $parent->id,
            );
        }

        $child = Category::query()
            ->withoutGlobalScopes()
            ->whereKey($childCategoryId)
            ->where('parent_id', $parent->id)
            ->first();

        if (! $child) {
            return new ResolvedContentTarget(
                AdBroadcast::CONTENT_TARGET_SUBCATEGORY,
                categoryId: (string) $parent->id,
                subcategoryId: $childCategoryId,
            );
        }

        return new ResolvedContentTarget(
            AdBroadcast::CONTENT_TARGET_SUBCATEGORY,
            categoryId: (string) $parent->id,
            subcategoryId: (string) $child->id,
        );
    }

    /**
     * @throws ValidationException
     */
    protected function resolveServiceMode(?string $serviceId): ResolvedContentTarget
    {
        if ($serviceId === null || $serviceId === '') {
            throw ValidationException::withMessages([
                'service_id' => translate('Service is required'),
            ]);
        }

        $service = Service::query()->withoutGlobalScopes()->find($serviceId);

        if (! $service) {
            return new ResolvedContentTarget(
                AdBroadcast::CONTENT_TARGET_SERVICE,
                serviceId: $serviceId,
            );
        }

        return new ResolvedContentTarget(
            AdBroadcast::CONTENT_TARGET_SERVICE,
            categoryId: $service->category_id ? (string) $service->category_id : null,
            subcategoryId: $service->sub_category_id ? (string) $service->sub_category_id : null,
            serviceId: (string) $service->id,
        );
    }
}
