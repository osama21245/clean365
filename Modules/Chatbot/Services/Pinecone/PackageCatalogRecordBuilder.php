<?php

namespace Modules\Chatbot\Services\Pinecone;

use Modules\ServiceManagement\Entities\Service;

/**
 * Builds Pinecone integrated-embedding records for Clean365 packages (services with visits_count).
 */
class PackageCatalogRecordBuilder
{
    public function buildPackage(Service $service): array
    {
        $service->loadMissing(['category', 'subCategory']);

        $name = trim((string) ($service->name ?? ''));
        $short = trim(strip_tags((string) ($service->short_description ?? '')));
        $desc = trim(strip_tags((string) ($service->description ?? '')));
        $category = trim((string) ($service->category?->name ?? ''));
        $subCategory = trim((string) ($service->subCategory?->name ?? ''));
        $visits = (int) ($service->visits_count ?? 0);
        $includes = is_array($service->service_includes) ? implode('، ', $service->service_includes) : '';
        $suitable = is_array($service->suitable_for) ? implode('، ', $service->suitable_for) : '';

        $textChunks = array_filter([
            'entity_type: package',
            "service_id: {$service->id}",
            $name !== '' ? "name: {$name}" : '',
            $short !== '' ? "short_description: {$short}" : '',
            $desc !== '' ? 'description: '.mb_substr($desc, 0, 800) : '',
            $category !== '' ? "category: {$category}" : '',
            $subCategory !== '' ? "sub_category: {$subCategory}" : '',
            $visits > 0 ? "visits_count: {$visits}" : '',
            $service->property_id ? "property_id: {$service->property_id}" : '',
            $includes !== '' ? "includes: {$includes}" : '',
            $suitable !== '' ? "suitable_for: {$suitable}" : '',
            'type_ar: باقة تنظيف | زيارات | اشتراك',
            'type_en: cleaning package | visits | subscription',
        ]);

        $textField = (string) config('services.pinecone.record_text_key', 'text');

        return [
            '_id' => 'package_'.$service->id,
            '_schema_version' => 'clean365_packages_v1',
            $textField => implode("\n", $textChunks),
            'type' => 'package',
            'entity_type' => 'package',
            'entity_id' => (string) $service->id,
            'service_id' => (string) $service->id,
            'item_name' => $name !== '' ? $name : null,
            'name_ar' => $name !== '' ? $name : null,
            'slug' => (string) ($service->slug ?? ''),
            'property_id' => $service->property_id ? (string) $service->property_id : null,
            'category_id' => $service->category_id ? (string) $service->category_id : null,
            'sub_category_id' => $service->sub_category_id ? (string) $service->sub_category_id : null,
            'visits_count' => $visits,
            'is_active' => (int) ($service->is_active ?? 0),
            'updated_at' => optional($service->updated_at)?->toIso8601String(),
        ];
    }
}
