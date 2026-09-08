<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Admin;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Rap2hpoutre\FastExcel\FastExcel;

class BookingController extends Controller
{
    private Booking $booking;
    private BookingStatusHistory $booking_status_history;
    private BookingScheduleHistory $booking_schedule_history;

    public function __construct(Booking $booking, BookingStatusHistory $booking_status_history, BookingScheduleHistory $booking_schedule_history)
    {
        $this->booking = $booking;
        $this->booking_status_history = $booking_status_history;
        $this->booking_schedule_history = $booking_schedule_history;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'zone_ids' => 'array',
            'from_date' => 'date',
            'to_date' => 'date',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'string' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookings = $this->booking->with(['customer'])
            ->when($request->has('string'), function ($query) use ($request) {
                $keys = explode(' ', base64_decode($request['string']));
                foreach ($keys as $key) {
                    $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                }
            })
            ->ofBookingStatus($request['booking_status'])
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request['from_date'])))
                    ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request['to_date'])));
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })->latest()->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $bookings), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'zone_ids' => 'array',
            'from_date' => 'date',
            'to_date' => 'date',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookings = $this->booking->with(['customer', 'provider'])
            ->when($request['booking_status'] != 'all', function ($query) use ($request) {
                $query->ofBookingStatus($request['booking_status']);
            })->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request['from_date'], $request['to_date']]);
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', [$request['sub_category_ids']]);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', [$request['category_ids']]);
            })->latest()->get();

        $formatted = $bookings->map(function ($item, $key) {
            $serviceAddress = is_string($item->service_address) ? json_decode($item->service_address) : $item->service_address;
            return [
                'SL' => $key + 1,
                'Booking ID' => $item->readable_id,
                'Booking Date' => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-M-Y h:i A') : '',
                'Service Location' => $item->service_location == 'provider' ? translate('Provider Location') : translate('Customer Location'),
                'Schedule Date' => $item->service_schedule ? \Carbon\Carbon::parse($item->service_schedule)->format('d-M-Y h:i A') : '',
                'Customer Name' => $item->customer ? trim(($item->customer->first_name ?? '') . ' ' . ($item->customer->last_name ?? '')) : ($serviceAddress?->contact_person_name ?? ''),
                'Customer Phone' => $item->customer ? $item->customer->phone : ($serviceAddress?->contact_person_number ?? ''),
                'Provider Name' => $item->provider?->company_name ?? translate('unassigned'),
                'Provider Phone' => $item->provider?->company_phone ?? '',
                'Total Amount' => with_currency_symbol($item->total_booking_amount),
                'Payment Status' => translate('paid'),
                'Booking Status' => translate($item->booking_status)];
        });

        if (!Storage::disk('public')->exists('/download')) {
            Storage::disk('public')->makeDirectory('/download');
        }
        $fileName = 'storage/app/public/download/bookings_list_' . date('Y_m_d') . '_' . rand(1000, 99999) . '.xlsx';
        return response()->json(response_formatter(DEFAULT_200, ['download_link' => (new FastExcel($formatted))->export($fileName)]), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = $this->booking->with([
            'detail.service', 'schedule_histories.user', 'status_histories.user', 'service_address', 'customer', 'provider', 'zone', 'serviceman.user'
        ])->where(['id' => $id])->first();
        if (isset($booking)) {
            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $booking_id
     * @return JsonResponse
     */
    public function status_update(Request $request, string $booking_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key'))]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->booking_status = $request['booking_status'];

            $booking_status_history = $this->booking_status_history;
            $booking_status_history->booking_id = $booking_id;
            $booking_status_history->changed_by = $request->user()->id;
            $booking_status_history->booking_status = $request['booking_status'];

            DB::transaction(function () use ($booking_status_history, $booking) {
                $booking->save();
                $booking_status_history->save();
            });

            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $booking_id
     * @return JsonResponse
     */
    public function schedule_update(Request $request, string $booking_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule' => 'required|date']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->service_schedule = $request['schedule'];

            $booking_schedule_history = $this->booking_schedule_history;
            $booking_schedule_history->booking_id = $booking_id;
            $booking_schedule_history->changed_by = $request->user()->id;
            $booking_schedule_history->schedule = $request['schedule'];

            DB::transaction(function () use ($booking_schedule_history, $booking) {
                $booking->save();
                $booking_schedule_history->save();
            });

            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }
}
