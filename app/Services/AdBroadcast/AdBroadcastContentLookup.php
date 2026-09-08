<?php

namespace App\Services\AdBroadcast;

use Illuminate\Database\Eloquent\Builder;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class AdBroadcastContentLookup
{
    /**
     * @return list<array{type: string, id: string, parent_id: ?string, label: string, breadcrumb: string}>
     */
    public function searchCategories(string $query, int $limit = 15): array
    {
        $needle = $this->likeNeedle($query);
        if ($needle === null) {
            return [];
        }

        $results = [];

        $parents = Category::query()
            ->withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('position', 1);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        foreach ($parents as $parent) {
            if ($this->nameMatches($parent, $needle)) {
                $results[] = [
                    'type' => 'parent',
                    'id' => (string) $parent->id,
                    'parent_id' => null,
                    'label' => (string) $parent->name,
                    'breadcrumb' => (string) $parent->name,
                ];
            }
        }

        $children = Category::query()
            ->withoutGlobalScopes()
            ->whereNotNull('parent_id')
            ->with(['parent'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        foreach ($children as $child) {
            if (! $this->nameMatches($child, $needle)) {
                continue;
            }

            $parentName = (string) ($child->parent?->name ?? '');
            $childName = (string) $child->name;

            $results[] = [
                'type' => 'subcategory',
                'id' => (string) $child->id,
                'parent_id' => $child->parent_id ? (string) $child->parent_id : null,
                'label' => $childName,
                'breadcrumb' => $parentName !== '' ? "{$parentName} › {$childName}" : $childName,
            ];
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * @return list<array{id: string, name: string, breadcrumb: string, parent_category_id: string, subcategory_id: string}>
     */
    public function searchServices(string $query, int $limit = 20): array
    {
        $needle = $this->likeNeedle($query);
        if ($needle === null) {
            return [];
        }

        $bare = trim($needle, '%');

        return Service::query()
            ->withoutGlobalScopes()
            ->select(['id', 'name', 'category_id', 'sub_category_id'])
            ->with(['category', 'subCategory'])
            ->where(function (Builder $q) use ($needle, $bare) {
                $q->where('name', 'like', $needle)
                    ->orWhereHas('translations', function (Builder $t) use ($bare) {
                        $t->where('key', 'name')->where('value', 'like', '%'.$bare.'%');
                    });
            })
            ->limit($limit)
            ->get()
            ->map(function (Service $service): array {
                $parentName = (string) ($service->category?->name ?? '');
                $subName = (string) ($service->subCategory?->name ?? '');
                $serviceName = (string) $service->name;
                $parts = array_filter([$parentName, $subName, $serviceName], fn (string $p) => $p !== '');

                return [
                    'id' => (string) $service->id,
                    'name' => $serviceName,
                    'breadcrumb' => implode(' › ', $parts),
                    'parent_category_id' => (string) ($service->category_id ?? ''),
                    'subcategory_id' => (string) ($service->sub_category_id ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{parent_category_id: string, subcategory_id: string, service_id: string, breadcrumb: string}|null
     */
    public function serviceSelectionSummary(string $serviceId): ?array
    {
        $service = Service::query()
            ->withoutGlobalScopes()
            ->with(['category', 'subCategory'])
            ->find($serviceId);

        if (! $service) {
            return null;
        }

        $parts = array_filter([
            (string) ($service->category?->name ?? ''),
            (string) ($service->subCategory?->name ?? ''),
            (string) $service->name,
        ], fn (string $p) => $p !== '');

        return [
            'parent_category_id' => (string) ($service->category_id ?? ''),
            'subcategory_id' => (string) ($service->sub_category_id ?? ''),
            'service_id' => (string) $service->id,
            'breadcrumb' => implode(' › ', $parts),
        ];
    }

    /**
     * @return array{parent_category_id: ?string, subcategory_id: ?string, breadcrumb: string}|null
     */
    public function categorySelectionSummary(?string $parentId, ?string $childId): ?array
    {
        if ($childId) {
            $child = Category::query()->withoutGlobalScopes()->with('parent')->find($childId);
            if (! $child) {
                return null;
            }

            $parent = $child->parent;

            return [
                'parent_category_id' => $child->parent_id ? (string) $child->parent_id : null,
                'subcategory_id' => (string) $child->id,
                'breadcrumb' => ($parent ? (string) $parent->name.' › ' : '').(string) $child->name,
            ];
        }

        if ($parentId) {
            $parent = Category::query()->withoutGlobalScopes()->find($parentId);
            if (! $parent) {
                return null;
            }

            return [
                'parent_category_id' => (string) $parent->id,
                'subcategory_id' => null,
                'breadcrumb' => (string) $parent->name,
            ];
        }

        return null;
    }

    protected function likeNeedle(string $query): ?string
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return null;
        }

        return '%'.$query.'%';
    }

    protected function nameMatches(Category $category, string $needle): bool
    {
        $bare = trim($needle, '%');
        $name = (string) $category->name;
        if ($name !== '' && mb_stripos($name, $bare) !== false) {
            return true;
        }

        $translated = Translation::query()
            ->where('translationable_type', Category::class)
            ->where('translationable_id', $category->id)
            ->where('key', 'name')
            ->pluck('value');

        foreach ($translated as $value) {
            if (is_string($value) && mb_stripos($value, $bare) !== false) {
                return true;
            }
        }

        return false;
    }
}
