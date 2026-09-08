<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use App\Dto\PublishAdBroadcastData;
use App\Models\AdBroadcast;
use App\Services\AdBroadcast\AdBroadcastListFilters;
use App\Services\AdBroadcast\AdBroadcastPublisher;
use App\Services\AiPush\AiPushNotificationScheduler;
use App\Services\AiPush\AiPushSettings;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;
use Modules\ZoneManagement\Entities\Zone;

class AdBroadcastController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request, AdBroadcastListFilters $filters, AiPushNotificationScheduler $scheduler): View
    {
        $this->authorize('push_notification_view');

        $query = AdBroadcast::query()->latest();
        $filters->apply(
            $query,
            $request->input('content_target'),
            $request->input('category_id'),
            $request->input('zone_filter'),
            $request->input('date_filter'),
        );

        $broadcasts = $query->paginate(pagination_limit())->appends($request->query());

        $settings = AiPushSettings::load();

        return view('promotionmanagement::admin.ad-broadcast.index', [
            'broadcasts' => $broadcasts,
            'settings' => $settings,
            'nextAiPushRun' => $scheduler->nextRunAt($settings),
            'aiPushFrequencies' => AiPushNotificationScheduler::FREQUENCIES,
            'zones' => Zone::query()->ofStatus(1)->latest()->get(),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View
    {
        $this->authorize('push_notification_add');

        $zones = Zone::query()->ofStatus(1)->latest()->get();
        $categories = Category::query()->withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('position', 1);
            })
            ->ofStatus(1)
            ->orderBy('name')
            ->get();
        $services = Service::query()->active()->orderBy('name')->limit(200)->get();

        return view('promotionmanagement::admin.ad-broadcast.create', compact('zones', 'categories', 'services'));
    }

    /**
     * @throws AuthorizationException
     */
    public function store(Request $request, AdBroadcastPublisher $publisher): RedirectResponse
    {
        $this->authorize('push_notification_add');

        $validated = $request->validate([
            'title' => 'required|array',
            'title.ar' => 'nullable|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string|max:500',
            'description.en' => 'nullable|string|max:500',
            'audiences' => 'required|array|min:1',
            'audiences.*' => ['required', 'string', Rule::in(['customers', 'providers', 'servicemen', 'guests'])],
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'uuid',
            'content_mode' => ['nullable', 'string', Rule::in(['none', 'category', 'service'])],
            'parent_category_id' => 'nullable|uuid',
            'child_category_id' => 'nullable|uuid',
            'service_id' => 'nullable|uuid',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $title = array_filter([
            'ar' => trim((string) ($validated['title']['ar'] ?? '')),
            'en' => trim((string) ($validated['title']['en'] ?? '')),
        ]);
        if ($title === []) {
            throw ValidationException::withMessages([
                'title' => translate('Title is required in at least one language'),
            ]);
        }

        $description = array_filter([
            'ar' => trim((string) ($validated['description']['ar'] ?? '')),
            'en' => trim((string) ($validated['description']['en'] ?? '')),
        ]);

        $storedImagePath = null;
        if ($request->hasFile('cover_image')) {
            $storedImagePath = $request->file('cover_image')->store('ad-broadcast', 'ad-media');
        }

        $zoneIds = $validated['zone_ids'] ?? [];
        if (in_array('all', $zoneIds, true)) {
            $zoneIds = Zone::query()->ofStatus(1)->pluck('id')->all();
        }

        try {
            $publisher->publish(new PublishAdBroadcastData(
                audiences: $validated['audiences'],
                zoneIds: $zoneIds !== [] ? array_values(array_map('strval', $zoneIds)) : null,
                title: $title,
                description: $description !== [] ? $description : null,
                storedImagePath: $storedImagePath,
                resendStoragePath: null,
                publisherUserId: auth()->id() ? (string) auth()->id() : null,
                explicitUserIds: null,
                contentMode: $validated['content_mode'] ?? 'none',
                parentCategoryId: $validated['parent_category_id'] ?? null,
                childCategoryId: $validated['child_category_id'] ?? null,
                serviceId: $validated['service_id'] ?? null,
            ));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        Toastr::success(translate('Broadcast queued'));

        return redirect()->route('admin.ad-broadcast.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function updateAiPush(Request $request, AiPushNotificationScheduler $scheduler): RedirectResponse
    {
        $this->authorize('push_notification_add');

        $frequency = (string) $request->input('ai_push_frequency', 'weekly');
        if ($scheduler->usesClockTime($frequency)) {
            $sendTimes = $request->input('ai_push_send_times', ['09:00']);
            if (is_string($sendTimes)) {
                $sendTimes = array_filter(array_map('trim', explode(',', $sendTimes)));
            }
            $sendTimes = array_values(array_unique(array_filter((array) $sendTimes)));
            sort($sendTimes);
            $request->merge([
                'ai_push_send_times' => $sendTimes !== [] ? $sendTimes : ['09:00'],
                'ai_push_time' => ($sendTimes[0] ?? '09:00'),
            ]);
        }

        $validated = $request->validate([
            'ai_push_enabled' => 'nullable|boolean',
            'ai_push_prompt' => 'nullable|string|max:5000',
            'ai_push_frequency' => ['required', 'string', Rule::in(array_keys(AiPushNotificationScheduler::FREQUENCIES))],
            'ai_push_time' => 'nullable|date_format:H:i',
            'ai_push_send_times' => 'nullable|array|max:12',
            'ai_push_send_times.*' => 'required|date_format:H:i',
            'ai_push_audiences' => 'required|array|min:1',
            'ai_push_audiences.*' => ['required', 'string', Rule::in(['customers', 'providers', 'servicemen', 'guests'])],
        ]);

        $settings = AiPushSettings::load();
        $sendTimes = $validated['ai_push_send_times'] ?? $scheduler->normalizeSendTimes($settings);
        $primaryTime = $validated['ai_push_time'] ?? $sendTimes[0] ?? '09:00';

        $frequencyChanged = (string) ($settings->ai_push_frequency ?? '') !== (string) $validated['ai_push_frequency'];
        $timesChanged = $scheduler->normalizeSendTimes($settings) !== array_values($sendTimes);

        $payload = [
            'ai_push_enabled' => $request->boolean('ai_push_enabled'),
            'ai_push_prompt' => $validated['ai_push_prompt'] ?? null,
            'ai_push_frequency' => $validated['ai_push_frequency'],
            'ai_push_time' => $primaryTime,
            'ai_push_send_times' => $sendTimes,
            'ai_push_audiences' => array_values(array_unique($validated['ai_push_audiences'])),
        ];

        if ($frequencyChanged || $timesChanged) {
            $payload['ai_push_daily_sent_slots'] = null;
        }

        $settings->update($payload);

        Toastr::success(translate('AI push settings updated'));

        return redirect()->route('admin.ad-broadcast.index');
    }
}
