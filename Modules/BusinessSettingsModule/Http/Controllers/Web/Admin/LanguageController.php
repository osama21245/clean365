<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LanguageController extends Controller
{
    use AuthorizesRequests;

    private BusinessSettings $businessSettings;

    public function __construct(BusinessSettings $businessSettings)
    {
        $this->businessSettings = $businessSettings;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|void
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('language_add');
        $request->validate([
            'name' => 'nullable',
            'code' => 'required'], [
            'code' => translate('Country code select is required')]);

        $language = business_config('system_language', 'business_information');
        $lan_data = [
            [
                'id' => 1,
                'name' => 'english',
                'direction' => 'ltr',
                'code' => 'en',
                'status' => 1,
                'default' => true
            ]
        ];
        if (!isset($language)) {
            BusinessSettings::updateOrCreate(['key_name' => 'system_language', 'settings_type' => 'business_information'], [
                'live_values' => $lan_data,
                'test_values' => $lan_data]);
            $language = business_config('system_language', 'business_information');
        }

        $langArray = [];
        $codes = [];
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] != $request['code']) {
                if (!array_key_exists('default', $data)) {
                    $default = array('default' => ($data['code'] == 'en') ? true : false);
                    $data = array_merge($data, $default);
                }
                $langArray[] = $data;
                $codes[] = $data['code'];
            }
        }
        $codes[] = $request['code'];

        if (!file_exists(base_path('resources/lang/' . $request['code']))) {
            mkdir(base_path('resources/lang/' . $request['code']), 0777, true);
        }

        $langFile = fopen(base_path('resources/lang/' . $request['code'] . '/' . 'lang.php'), "w") or die("Unable to open file!");
        $read = file_get_contents(base_path('resources/lang/en/lang.php'));
        fwrite($langFile, $read);

        $langArray[] = [
            'id' => count($language?->live_values) + 1,
            'name' => $request['name'],
            'code' => $request['code'],
            'direction' => $request['direction'],
            'status' => 1,
            'default' => false];

        $this->businessSettings->updateOrCreate(['key_name' => 'system_language'], [
            'live_values' => $langArray,
            'test_values' => $langArray]);

        Toastr::success(translate('Language Added!'));
        return back();
    }

    /**
     * @throws AuthorizationException
     */
    public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('language_manage_status');
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        $langArray = [];
        foreach ($language?->live_values as $key => $data) {

            if ($data['code'] == $request['code']) {
                if (array_key_exists('default', $data) && $data['default'] == true) {
                    return response()->json(['error' => 403]);
                }
                $lang = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $request['code'],
                    'status' => $data['status'] == 1 ? 0 : 1,
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false))];
                $langArray[] = $lang;
            } else {
                $lang = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false))];
                $langArray[] = $lang;
            }
        }
        $this->businessSettings->where('key_name', 'system_language')->update([
            'live_values' => $langArray,
            'test_values' => $langArray]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function updateDefaultStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('language_manage_status');
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        $langArray = [];
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $request['code']) {
                $lang = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => 1,
                    'default' => true];
                $langArray[] = $lang;
            } else {
                $lang = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => false];
                $langArray[] = $lang;
            }
        }
        $this->businessSettings->where('key_name', 'system_language')->update([
            'live_values' => $langArray,
            'test_values' => $langArray]);

        $direction = $this->businessSettings->where('key_name', 'site_direction')->first();
        $direction = $direction->value ?? 'ltr';
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        foreach ($language?->live_values ?? [] as $key => $data) {
            if ($data['code'] == $request['code']) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }
        session()->forget('language_settings');
        language_load();
        session()->put('local', $request['code']);
        session()->put('site_direction', $direction);

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('language_update');

        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        $langArray = [];

        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $request['code']) {
                $lang = [
                    'id' => $data['id'],
                    'direction' => $request['direction'] ?? 'ltr',
                    'name'      => $request['name'] ?? $data['name'],
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false))];
                $langArray[] = $lang;
            } else {
                $lang = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false))];
                $langArray[] = $lang;
            }
        }
        $this->businessSettings->where('key_name', 'system_language')->update([
            'live_values' => $langArray,
            'test_values' => $langArray]);

        Toastr::success(translate('Language updated'));
        return back();
    }

    public function convertArrayToCollection($lang, $items, $perPage = null, $page = null, $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $options = [
            "path" => route('admin.language.translate', [$lang]),
            "pageName" => "page"
        ];
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function translate(Request $request, $lang): Factory|View|Application
    {
//        $this->authorize('configuration_view');
        $searchTerm = $request['search'];
        $fullData = load_translation_messages($lang);
        $totalMessages = count($fullData);
        $fullData = array_filter($fullData, fn($value) => !is_null($value) && $value !== '');

        // If a search term is provided, filter the array based on the search term
        if (!empty($searchTerm)) {
            $fullData = array_filter($fullData, function ($value, $key) use ($searchTerm) {
                return (stripos($value, $searchTerm) !== false) || (stripos(ucfirst(str_replace('_', ' ', remove_invalid_charcaters($key))), $searchTerm) !== false);
            }, ARRAY_FILTER_USE_BOTH);
        }


        ksort($fullData);
        $fullData = $this->convertArrayToCollection($lang, $fullData, config('default_pagination'));

        return view('businesssettingsmodule::admin.translation-page', compact('lang', 'fullData','totalMessages'));
    }

    public function translateKeyRemove(Request $request, $lang): void
    {
        $fullData = load_translation_messages($lang);
        unset($fullData[$request['key']]);
        save_translation_messages($lang, $fullData);
    }

    public function translateSubmit(Request $request, $lang): void
    {
        $this->updateAdvancedSearchKeyWords($lang, $request['key'], $request['value']);
        $fullData = load_translation_messages($lang);
        $fullData[urldecode($request['key'])] = $request['value'];
        save_translation_messages($lang, $fullData);
    }

    public function autoTranslate(Request $request, $lang): \Illuminate\Http\JsonResponse
    {
        $languageCode = getLanguageCode($lang);
        $key = (string)$request['key'];
        $fullData = load_translation_messages($lang);
        $englishData = load_translation_messages('en');
        $sourceText = $this->resolveTranslationSourceText($key, $englishData, $fullData);
        $translated = $sourceText !== ''
            ? ($this->translateBatch([$sourceText], 'en', $languageCode)[$sourceText] ?? $sourceText)
            : '';

        $this->updateAdvancedSearchKeyWords($lang, $key, $translated);
        $fullData[$key] = $translated;
        save_translation_messages($lang, $fullData);

        return response()->json([
            'translated_data' => $translated
        ]);
    }

    public function autoTranslateAll(Request $request, $lang): \Illuminate\Http\JsonResponse
    {
        try {
            if ($lang === 'en') {
                return response()->json([
                    'message' => translate('All_data_are_translated'),
                    'data' => 'success'
                ]);
            }

            $start_time = now();
            $languageCode = getLanguageCode($lang);
            $fullData = load_translation_messages($lang);
            $englishData = load_translation_messages('en');
            $queueFile = $this->translationQueueFile($lang);
            $requestedTotal = (int)$request->input('translating_count', 0);

            if ($requestedTotal <= 0 || !file_exists($queueFile)) {
                $pendingKeys = $this->buildAutoTranslateQueue($englishData, $fullData);
                $this->saveTranslationQueue($lang, $pendingKeys);
            } else {
                $pendingKeys = $this->loadTranslationQueue($lang);
            }

            if (empty($pendingKeys)) {
                $this->clearTranslationQueue($lang);

                return response()->json([
                    'message' => translate('All_data_are_translated'),
                    'data' => 'success'
                ]);
            }

            $totalItems = max($requestedTotal, count($pendingKeys));
            $batchKeys = array_slice($pendingKeys, 0, 60);
            $remainingKeys = array_slice($pendingKeys, count($batchKeys));
            $sourceTextsByKey = [];

            foreach ($batchKeys as $key) {
                $sourceTextsByKey[$key] = $this->resolveTranslationSourceText($key, $englishData, $fullData);
            }

            $translatedTexts = $this->translateBatch(array_values($sourceTextsByKey), 'en', $languageCode);
            $translatedByKey = [];

            foreach ($sourceTextsByKey as $key => $sourceText) {
                $translatedByKey[$key] = $translatedTexts[$sourceText] ?? $sourceText;
                $fullData[$key] = $translatedByKey[$key];
            }

            save_translation_messages($lang, $fullData);
            $this->updateAdvancedSearchKeyWordsBulk($lang, $translatedByKey);
            $this->saveTranslationQueue($lang, $remainingKeys);

            $remainingCount = count($remainingKeys);
            $percentage = 100 - (($remainingCount / max($totalItems, 1)) * 100);
            $timeTaken = max($start_time->diffInMilliseconds(now()), 1);
            $itemsPerSecond = (count($batchKeys) * 1000) / $timeTaken;
            $secondsRemaining = $itemsPerSecond > 0 ? (int)ceil($remainingCount / $itemsPerSecond) : 0;

            if ($remainingCount === 0) {
                $this->clearTranslationQueue($lang);
            }

            return response()->json([
                'message' => translate('translating'),
                'data' => 'translating',
                'total' => $totalItems,
                'percentage' => round($percentage, 1),
                'hours' => intdiv($secondsRemaining, 3600),
                'minutes' => intdiv($secondsRemaining % 3600, 60),
                'seconds' => $secondsRemaining % 60,
                'status' => $remainingCount > 0 ? 'pending' : 'done'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'data' => 'error'
            ]);
        }
    }

    public function delete(Request $request, $lang): RedirectResponse|JsonResponse
    {
        $this->authorize('language_delete');
        $language = $this->businessSettings->where('key_name', 'system_language')->first();

        $defaultDelete = false;
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $lang && array_key_exists('default', $data) && $data['default'] == true) {
                $defaultDelete = true;
            }
        }

        $langArray = [];
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] != $lang) {
                $languageData = [
                    'id' => $data['id'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => ($defaultDelete == true && $data['code'] == 'en') ? 1 : $data['status'],
                    'default' => ($defaultDelete == true && $data['code'] == 'en') ? true : (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false))];
                array_push($langArray, $languageData);
            }
        }

        $this->businessSettings->where('key_name', 'system_language')->update([
            'live_values' => $langArray,
            'test_values' => $langArray]);

        $dir = base_path('resources/lang/' . $lang);
        if (File::isDirectory($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }


        $languages = array();
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        foreach ($language?->live_values as $key => $data) {
            if ($data != $lang) {
                array_push($languages, $data);
            }
        }
        if (in_array('en', $languages)) {
            unset($languages[array_search('en', $languages)]);
        }
        array_unshift($languages, 'en');

        $this->businessSettings->updateOrCreate(['key_name' => 'language'], [
            'live_values' => $languages,
            'test_values' => $languages]);

        if ($request->ajax()) {
            return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
        } else {
            Toastr::success(translate('Removed Successfully!'));
            return back();
        }
    }

    public function lang($local): RedirectResponse
    {
        $direction = $this->businessSettings->where('key_name', 'site_direction')->first();
        $direction = $direction->live_values ?? 'ltr';
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $local) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }
        session()->forget('language_settings');
        language_load();
        session()->put('local', $local);
        session()->put('site_direction', $direction);
        return redirect()->back();
    }



    public function updateAdvancedSearchKeyWords($lang, $key, $result): void
    {
        $this->updateAdvancedSearchKeyWordsBulk($lang, [$key => $result]);
    }

    function normalize($str) {
        $str = strtolower($str);
        // Replace all non-alphanumeric with space
        $str = preg_replace('/[^a-z0-9]+/', ' ', $str);
        // Trim and reduce multiple spaces to single
        return trim(preg_replace('/\s+/', ' ', $str));
    }

    public function removeUnderscore($input)
    {
        if (strpos($input, '_') !== false) {
            return str_replace('_', ' ', $input);
        }
        return $input;
    }

    private function shouldAutoTranslate(mixed $targetValue, mixed $englishValue): bool
    {
        $englishValue = $this->normalizeTranslationSource((string)$englishValue);
        $targetValue = $this->normalizeTranslationSource((string)$targetValue);

        return $englishValue !== '' && ($targetValue === '' || $targetValue === $englishValue);
    }

    private function resolveTranslationSourceText(string $key, array $englishData, array $targetData = []): string
    {
        $source = $englishData[$key] ?? $targetData[$key] ?? $key;
        $source = $this->normalizeTranslationSource((string)$source);

        return $source !== '' ? $source : humanize_translation_key($key);
    }

    private function normalizeTranslationSource(string $text): string
    {
        return normalize_translation_text($text);
    }

    private function translateBatch(array $texts, string $sourceLanguage, string $targetLanguage): array
    {
        $translations = [];
        $uniqueTexts = [];
        $preparedTexts = [];

        foreach ($texts as $text) {
            $normalized = $this->normalizeTranslationSource((string)$text);
            if ($normalized === '') {
                $translations[$text] = '';
                continue;
            }

            if (!array_key_exists($normalized, $preparedTexts)) {
                $preparedTexts[$normalized] = $this->maskTranslationTokens($normalized);
                $uniqueTexts[] = $normalized;
            }
        }

        foreach (array_chunk($uniqueTexts, 12) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $preparedTexts, $sourceLanguage, $targetLanguage) {
                $requests = [];

                foreach ($chunk as $text) {
                    $requestKey = md5($text);
                    $query = http_build_query([
                        'client' => 'gtx',
                        'ie' => 'UTF-8',
                        'oe' => 'UTF-8',
                        'sl' => $sourceLanguage,
                        'tl' => $targetLanguage,
                        'hl' => 'hl',
                        'q' => $preparedTexts[$text]['text']]) . '&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at';

                    $requests[] = $pool->as($requestKey)
                        ->timeout(20)
                        ->retry(1, 200)
                        ->get('https://translate.googleapis.com/translate_a/single?' . $query);
                }

                return $requests;
            });

            foreach ($chunk as $text) {
                $response = $responses[md5($text)] ?? null;
                $translated = $text;

                if ($response && $response->successful()) {
                    $payload = $response->json();
                    $translatedSegments = collect($payload[0] ?? [])
                        ->pluck(0)
                        ->filter(fn ($segment) => filled($segment))
                        ->implode('');

                    $translated = $translatedSegments !== '' ? $translatedSegments : $text;
                }

                $translations[$text] = $this->restoreTranslationTokens(
                    $this->normalizeTranslationSource(str_replace('_', ' ', (string)$translated)),
                    $preparedTexts[$text]['tokens']
                );
            }
        }

        return $translations;
    }

    private function maskTranslationTokens(string $text): array
    {
        $tokens = [];
        $index = 0;

        $maskedText = preg_replace_callback(
            '/(\{\{[^}]+\}\}|:\w+|%\d*\$?[sd])/u',
            function ($matches) use (&$tokens, &$index) {
                $token = 'ZXQPH' . $index . 'QXZ';
                $tokens[$token] = $matches[0];
                $index++;
                return $token;
            },
            $text
        );

        return [
            'text' => $maskedText,
            'tokens' => $tokens];
    }

    private function restoreTranslationTokens(string $text, array $tokens): string
    {
        return strtr($text, $tokens);
    }

    private function updateAdvancedSearchKeyWordsBulk(string $lang, array $translations): void
    {
        if ($lang === 'en' || empty($translations)) {
            return;
        }

        $normalizedTranslations = [];
        foreach ($translations as $key => $value) {
            $normalizedTranslations[$this->normalize((string)$key)] = $value;
        }

        foreach (['admin', 'provider'] as $panel) {
            $englishJsonPath = public_path("json/{$panel}/lang/en.json");
            $translatedJsonPath = public_path("json/{$panel}/lang/{$lang}.json");

            if (!file_exists($englishJsonPath)) {
                continue;
            }

            if (!file_exists($translatedJsonPath)) {
                if (!file_exists(dirname($translatedJsonPath))) {
                    File::makeDirectory(dirname($translatedJsonPath), 0777, true, true);
                }

                file_put_contents($translatedJsonPath, file_get_contents($englishJsonPath));
            }

            $json = json_decode(file_get_contents($translatedJsonPath), true);
            if (!is_array($json)) {
                throw new \RuntimeException("Failed to decode {$panel} language keyword JSON.");
            }

            $walker = function (&$node) use (&$walker, $normalizedTranslations) {
                if (!is_array($node)) {
                    return;
                }

                if (isset($node['page_title']) && array_key_exists('page_title_value', $node)) {
                    $pageTitleKey = $this->normalize((string)$node['page_title']);
                    if (isset($normalizedTranslations[$pageTitleKey])) {
                        $node['page_title_value'] = $normalizedTranslations[$pageTitleKey];
                    }
                }

                foreach ($node as $field => &$value) {
                    if (is_array($value)) {
                        $walker($value);
                        continue;
                    }

                    if (!is_string($value) || in_array($field, ['page_title', 'page_title_value'], true)) {
                        continue;
                    }

                    $normalizedValue = $this->normalize($value);
                    if (isset($normalizedTranslations[$normalizedValue])) {
                        $value = $normalizedTranslations[$normalizedValue];
                    }
                }
            };

            $walker($json);

            file_put_contents($translatedJsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function translationQueueFile(string $lang): string
    {
        return base_path('resources/lang/' . $lang . '/auto-translate-queue.php');
    }

    private function loadTranslationQueue(string $lang): array
    {
        $queueFile = $this->translationQueueFile($lang);

        if (!file_exists($queueFile)) {
            return [];
        }

        $queue = include $queueFile;

        return is_array($queue) ? array_values($queue) : [];
    }

    private function saveTranslationQueue(string $lang, array $keys): void
    {
        $queueFile = $this->translationQueueFile($lang);
        file_put_contents($queueFile, "<?php return " . var_export(array_values($keys), true) . ";");
    }

    private function clearTranslationQueue(string $lang): void
    {
        $queueFile = $this->translationQueueFile($lang);

        if (file_exists($queueFile)) {
            @unlink($queueFile);
        }

        $legacyQueueFile = base_path('resources/lang/' . $lang . '/new-lang.php');
        if (file_exists($legacyQueueFile)) {
            @unlink($legacyQueueFile);
        }
    }

    private function buildAutoTranslateQueue(array $englishData, array $fullData): array
    {
        $pendingKeys = [];

        foreach ($englishData as $key => $englishValue) {
            $targetValue = $fullData[$key] ?? '';

            if ($this->shouldAutoTranslate($targetValue, $englishValue)) {
                $pendingKeys[] = $key;
            }
        }

        usort($pendingKeys, function (string $left, string $right) use ($englishData) {
            return $this->translationPriority($englishData[$right] ?? $right) <=> $this->translationPriority($englishData[$left] ?? $left);
        });

        return $pendingKeys;
    }

    private function translationPriority(string $text): int
    {
        $text = $this->normalizeTranslationSource($text);
        $score = 0;

        if (preg_match('/\p{Ll}/u', $text)) {
            $score += 4;
        }

        if (str_contains($text, ' ')) {
            $score += 3;
        }

        if (strlen($text) >= 12) {
            $score += 2;
        }

        if (preg_match('/[@\/\\\\]/u', $text)) {
            $score -= 4;
        }

        if (preg_match('/\.[a-z0-9]{2,5}$/iu', $text)) {
            $score -= 4;
        }

        if (!preg_match('/\p{L}/u', $text)) {
            $score -= 6;
        }

        return $score;
    }
}
