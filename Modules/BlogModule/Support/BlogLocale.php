<?php

namespace Modules\BlogModule\Support;

use Illuminate\Database\Eloquent\Model;

class BlogLocale
{
    public static function of(?Model $model, string $key, string $locale): string
    {
        if ($model === null) {
            return '';
        }

        if ($model->relationLoaded('translations')) {
            foreach ($model->translations as $translation) {
                if (($translation->key ?? null) === $key && ($translation->locale ?? null) === $locale) {
                    return (string) ($translation->value ?? '');
                }
            }
        } elseif (method_exists($model, 'translations')) {
            $translation = $model->translations()
                ->where('key', $key)
                ->where('locale', $locale)
                ->first();

            if ($translation) {
                return (string) ($translation->value ?? '');
            }
        }

        $raw = $model->getAttributes()[$key] ?? null;

        return is_string($raw) ? $raw : '';
    }
}
