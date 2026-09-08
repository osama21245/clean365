<?php

namespace Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops;

use App\Traits\UploadSizeHelperTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Illuminate\Support\Facades\DB;
use Modules\ServicemanModule\Entities\BookingAttendance;
use Modules\ServicemanModule\Entities\BookingExecutionNote;
use Modules\ServicemanModule\Entities\BookingSupportRequest;
use Modules\ServicemanModule\Http\Traits\BookingTeamAccessTrait;
use Modules\ServicemanModule\Http\Traits\SupervisorOpsTrait;

class JobController extends Controller
{
    use SupervisorOpsTrait, BookingTeamAccessTrait, UploadSizeHelperTrait;

    private Booking $booking;
    private BookingExecutionNote $executionNote;
    private BookingSupportRequest $supportRequest;
    private BookingStatusHistory $bookingStatusHistory;

    public function __construct(
        Booking $booking,
        BookingExecutionNote $executionNote,
        BookingSupportRequest $supportRequest,
        BookingStatusHistory $bookingStatusHistory
    ) {
        $this->booking = $booking;
        $this->executionNote = $executionNote;
        $this->supportRequest = $supportRequest;
        $this->bookingStatusHistory = $bookingStatusHistory;
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'field_status' => 'nullable|in:all,' . implode(',', $this->fieldStatusKeys()),
            'search' => 'nullable|string|max:191',
            'date' => 'nullable|date']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;
        $base = $this->booking
            ->where('provider_id', $providerId)
            ->whereNotNull('team_id')
            ->where('booking_status', '!=', 'canceled');

        $summaryDate = $request->filled('date') ? Carbon::parse($request['date']) : Carbon::today();
        $summaryQuery = (clone $base)->whereDate('service_schedule', $summaryDate->toDateString());
        $summaryTotal = (clone $summaryQuery)->count();
        $summaryCompleted = (clone $summaryQuery)->where(function ($q) {
            $q->where('field_status', 'completed')->orWhere('booking_status', 'completed');
        })->count();

        $summary = ['total' => $summaryTotal, 'progress_percent' => $summaryTotal > 0 ? (int) round(($summaryCompleted / $summaryTotal) * 100) : 0];
        foreach ($this->fieldStatusKeys() as $status) {
            $summary[$status] = (clone $summaryQuery)->where('field_status', $status)->count();
        }

        $jobs = (clone $base)
            ->with(['customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address', 'attendances'])
            ->when(($request['field_status'] ?? 'all') !== 'all', function ($query) use ($request) {
                $query->where('field_status', $request['field_status']);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('service_schedule', Carbon::parse($request['date'])->toDateString());
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request['search'];
                $query->where(function ($inner) use ($search) {
                    $inner->where('readable_id', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('team', function ($teamQuery) use ($search) {
                            $teamQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('service_schedule')
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        $jobs->setCollection(
            $jobs->getCollection()->map(fn ($booking) => $this->transformJobCard($booking))
        );

        return response()->json(response_formatter(DEFAULT_200, [
            'summary' => $summary,
            'jobs' => $jobs]), 200);
    }

    public function show(Request $request, string $bookingId): JsonResponse
    {
        $booking = $this->ownedBooking($request, $bookingId, [
            'customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address',
            'attendances.serviceman.user', 'executionNotes.user']);

        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        return response()->json(response_formatter(DEFAULT_200, $this->transformJobDetail($booking)), 200);
    }

    public function markSeen(Request $request, string $bookingId): JsonResponse
    {
        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        if (empty($booking->supervisor_seen_at)) {
            $booking->supervisor_seen_at = now();
            $booking->supervisor_seen_by = $request->user()->id;
            $booking->save();
        }

        return response()->json(response_formatter(BOOKING_SUPERVISOR_SEEN_200, $this->transformJobDetail($booking->fresh([
            'customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address',
            'attendances.serviceman.user', 'executionNotes.user']))), 200);
    }

    public function updateFieldStatus(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field_status' => 'required|in:' . implode(',', $this->fieldStatusKeys())]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $previousFieldStatus = $booking->field_status;
        $next = $request['field_status'];
        if (!$this->canTransitionFieldStatus($previousFieldStatus, $next)) {
            return response()->json(response_formatter(DEFAULT_400, null, [[
                'error_code' => 'invalid_field_status_transition',
                'message' => translate('Invalid field status transition')]]), 400);
        }

        $booking->field_status = $next;
        $this->syncBookingStatusFromFieldStatus($booking, $next);
        $this->applyProgressFromFieldStatus($booking, $next);
        $booking->save();

        $history = new $this->bookingStatusHistory;
        $history->booking_id = $booking->id;
        $history->changed_by = $request->user()->id;
        $history->booking_status = $booking->booking_status;
        $history->save();

        $broadcast = app(\App\Services\Tracking\TrackingBroadcastService::class);
        $wasTracking = $broadcast->isTrackingFieldStatus($previousFieldStatus);
        $isTracking = $broadcast->isTrackingFieldStatus($next);

        if ($isTracking && !$wasTracking) {
            $broadcast->broadcastTrackingStarted($booking->fresh());
        } elseif ($isTracking && $wasTracking && $previousFieldStatus !== $next) {
            // Still tracking (e.g. on_the_way → arrived) — refresh session payload.
            $broadcast->broadcastTrackingStarted($booking->fresh());
        } elseif (!$isTracking && $wasTracking) {
            $broadcast->broadcastTrackingEnded($booking->fresh(), $next === 'completed' ? 'completed' : 'left_route');
        } elseif ($next === 'completed') {
            $broadcast->broadcastTrackingEnded($booking->fresh(), 'completed');
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200, $this->transformJobDetail($booking->fresh([
            'customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address',
            'attendances.serviceman.user', 'executionNotes.user']))), 200);
    }

    public function updateProgress(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'progress_percent' => 'required|integer|min:0|max:100']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $booking->progress_percent = (int) $request['progress_percent'];
        $booking->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200, $this->transformJobDetail($booking->fresh([
            'customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address',
            'attendances.serviceman.user', 'executionNotes.user']))), 200);
    }

    public function uploadImages(Request $request, string $bookingId, string $stage): JsonResponse
    {
        if (!in_array($stage, ['before', 'during', 'after'], true)) {
            return response()->json(response_formatter(DEFAULT_400, null, [[
                'error_code' => 'invalid_stage',
                'message' => translate('Invalid image stage')]]), 400);
        }

        $field = $stage . '_images';
        $check = $this->validateUploadedFile($request, ['images']);
        if ($check !== true) {
            return $check;
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $uploaded = $this->uploadTaskImages($request->file('images'), 'booking/ops/');
        $booking->{$field} = $this->mergeTaskImages($booking->{$field}, $uploaded);
        $booking->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200, $this->transformJobDetail($booking->fresh([
            'customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address',
            'attendances.serviceman.user', 'executionNotes.user']))), 200);
    }

    public function storeNote(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $note = $this->executionNote->create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'note' => $request['note']]);

        return response()->json(response_formatter(DEFAULT_STORE_200, $note->load('user')), 200);
    }

    public function attendance(Request $request, string $bookingId): JsonResponse
    {
        $booking = $this->ownedBooking($request, $bookingId, [
            'team.servicemen.user', 'attendances']);

        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $summary = $this->attendanceSummary($booking);
        $attendanceByServiceman = $booking->attendances->keyBy('serviceman_id');

        $members = ($booking->team?->servicemen ?? collect())->map(function ($serviceman) use ($attendanceByServiceman) {
            $record = $attendanceByServiceman->get($serviceman->id);

            return [
                'id' => $serviceman->id,
                'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                'phone' => $serviceman->user?->phone,
                'profile_image' => $serviceman->user?->profile_image_full_path,
                'is_attended' => (bool) ($record?->is_attended),
                'attended_at' => $record?->is_attended ? $record->attended_at : null];
        })->values();

        return response()->json(response_formatter(DEFAULT_200, [
            'summary' => $summary,
            'members' => $members]), 200);
    }

    public function storeAttendance(Request $request, string $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'serviceman_id' => 'required|uuid',
            'is_attended' => 'required|boolean']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId, [
            'team.servicemen', 'attendances']);

        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $servicemanId = $request['serviceman_id'];
        $onTeam = ($booking->team?->servicemen ?? collect())->contains(fn ($serviceman) => $serviceman->id === $servicemanId);
        if (!$onTeam) {
            return response()->json(response_formatter(DEFAULT_400, null, [[
                'error_code' => 'invalid_serviceman',
                'message' => translate('Serviceman must belong to the assigned team')]]), 400);
        }

        $isAttended = $request->boolean('is_attended');

        DB::transaction(function () use ($booking, $servicemanId, $isAttended) {
            $attendance = $booking->attendances->firstWhere('serviceman_id', $servicemanId);
            if (!$attendance) {
                $attendance = new BookingAttendance();
                $attendance->booking_id = $booking->id;
                $attendance->serviceman_id = $servicemanId;
            }
            $attendance->is_attended = $isAttended;
            $attendance->attended_at = $isAttended ? now() : null;
            $attendance->save();
        });

        return $this->attendance($request, $bookingId);
    }

    public function storeSupport(Request $request, string $bookingId): JsonResponse
    {
        $check = $this->validateUploadedFile($request, ['attachments']);
        if ($check !== true) {
            return $check;
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', array_column(BOOKING_SUPPORT_REQUEST_TYPES, 'key')),
            'message' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->ownedBooking($request, $bookingId);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $attachments = $request->hasFile('attachments')
            ? $this->uploadTaskImages($request->file('attachments'), 'booking/support/')
            : null;

        $support = $this->supportRequest->create([
            'booking_id' => $booking->id,
            'provider_id' => $request->user()->provider->id,
            'type' => $request['type'],
            'message' => $request['message'] ?? null,
            'attachments' => $attachments,
            'status' => 'open']);

        return response()->json(response_formatter(DEFAULT_STORE_200, $support), 200);
    }

    private function ownedBooking(Request $request, string $bookingId, array $with = []): ?Booking
    {
        return $this->booking->with($with)
            ->where(['id' => $bookingId, 'provider_id' => $request->user()->provider->id])
            ->first();
    }
}
