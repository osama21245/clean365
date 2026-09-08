<?php

namespace Modules\BookingModule\Http\Controllers\Web\Admin;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingAdditionalInformation;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingRepeat;
use Modules\BookingModule\Entities\BookingRepeatDetails;
use Modules\BookingModule\Entities\BookingRepeatHistory;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Illuminate\Http\RedirectResponse;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{

    private Booking $booking;
    private BookingRepeat $bookingRepeat;
    private BookingStatusHistory $bookingStatusHistory;
    private BookingRepeatHistory $bookingRepeatHistory;
    private BookingScheduleHistory $bookingScheduleHistory;
    private $subscribedSubCategories;
    private Category $category;
    private Zone $zone;
    private Serviceman $serviceman;
    private Provider $provider;
    private UserAddress $userAddress;
    private BookingDetail $bookingDetails;
    private BookingAdditionalInformation $bookingAdditionalInformation;
    private BookingRepeatDetails $bookingRepeatDetail;

    use BookingTrait;
    use AuthorizesRequests;

    public function __construct(Booking $booking, BookingRepeatDetails $bookingRepeatDetail, BookingRepeatHistory $bookingRepeatHistory, BookingRepeat $bookingRepeat, BookingDetail $bookingDetails, BookingStatusHistory $bookingStatusHistory, BookingScheduleHistory $bookingScheduleHistory, SubscribedService $subscribedService, Category $category, Zone $zone, Serviceman $serviceman, Provider $provider, UserAddress $userAddress, BookingAdditionalInformation $bookingAdditionalInformation)
    {
        $this->booking = $booking;
        $this->bookingRepeat = $bookingRepeat;
        $this->bookingRepeatDetail = $bookingRepeatDetail;
        $this->bookingRepeatHistory = $bookingRepeatHistory;
        $this->bookingDetails = $bookingDetails;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->bookingScheduleHistory = $bookingScheduleHistory;
        $this->category = $category;
        $this->zone = $zone;
        $this->serviceman = $serviceman;
        $this->provider = $provider;
        $this->userAddress = $userAddress;
        $this->bookingAdditionalInformation = $bookingAdditionalInformation;
        try {
            $this->subscribedSubCategories = $subscribedService->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();
        } catch (\Exception $exception) {
            $this->subscribedSubCategories = $subscribedService->pluck('sub_category_id')->toArray();
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(Request $request): Renderable
    {
        $this->authorize('booking_view');
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all']);

        $queryParams = $request->only(['zone_ids', 'category_ids', 'sub_category_ids', 'start_date', 'end_date', 'search', 'service_type', 'payment_status']);
        $filterCounter = collect($queryParams)->filter(function ($value, $key) {
            if (in_array($key, ['service_type', 'search'], true)) {
                return false;
            }
            if ($key === 'payment_status' && (is_null($value) || $value === '' || $value === 'all')) {
                return false;
            }

            return !is_null($value) && $value !== '';
        })->count();
        $allowedStatuses = ['accepted', 'ongoing', 'completed', 'canceled', 'all'];
        $bookingStatus = $request->input('booking_status', 'accepted');
        if (!in_array($bookingStatus, $allowedStatuses, true)) {
            $bookingStatus = 'accepted';
        }
        $queryParams['booking_status'] = $bookingStatus;
        $queryParams['booking_type'] = $request->input('booking_type', '');
        // Repeat booking UI temporarily hidden — force away from "repeat"
        $serviceType = $request->input('service_type', 'all');
        if ($serviceType === 'repeat') {
            $serviceType = 'all';
        }
        $queryParams['service_type'] = $serviceType;
        $queryParams['provider_assigned'] = $request->input('provider_assigned', '');
        $queryParams['payment_status'] = $request->input('payment_status', 'all');

        if (empty($queryParams['start_date'])) {
            $queryParams['start_date'] = null;
        }
        if (empty($queryParams['end_date'])) {
            $queryParams['end_date'] = null;
        }

        if (!empty($queryParams['provider_assigned']) && $queryParams['provider_assigned'] !== 'all') {
            $filterCounter++;
        }

        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;
        $statusCounts = [
            'accepted' => $this->booking->adminAcceptedBookings($maxBookingAmount)->ofBookingStatus('accepted')->count(),
            'ongoing' => $this->booking->ofBookingStatus('ongoing')->count(),
            'completed' => $this->booking->ofBookingStatus('completed')->count(),
            'canceled' => $this->booking->ofBookingStatus('canceled')->count()];

        $bookings = $this->booking
            ->with(['customer', 'provider', 'service_address'])
            ->when(filled($request->input('search')), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where(function ($query) use ($search) {
                    $keys = preg_split('/\s+/', $search) ?: [];
                    foreach ($keys as $key) {
                        if ($key === '') {
                            continue;
                        }
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('customer', function ($q) use ($key) {
                                $q->where('first_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $key . '%')
                                    ->orWhere('email', 'LIKE', '%' . $key . '%');
                            })
                            ->orWhereHas('service_address', function ($q) use ($key) {
                                $q->where('contact_person_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('contact_person_number', 'LIKE', '%' . $key . '%');
                            })
                            ->orWhereHas('provider', function ($q) use ($key) {
                                $q->where('company_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('company_phone', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($bookingStatus != 'all', function ($query) use ($bookingStatus, $maxBookingAmount) {
                $query->when($bookingStatus == 'accepted', function ($query) use ($maxBookingAmount) {
                    $query->adminAcceptedBookings($maxBookingAmount);
                })->ofBookingStatus($bookingStatus);
            })
            ->when($serviceType != 'all', function ($query) use ($serviceType) {
                return $query->ofRepeatBookingStatus($serviceType === 'regular' ? 0 : null);
            })
            ->when($request['provider_assigned'] == 'assigned', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNotNull('provider_id')
                        ->orWhereHas('repeat', function ($q) {
                            $q->whereNotNull('provider_id');
                        });
                });
            })
            ->when($request['provider_assigned'] == 'unassigned', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('provider_id');
                });
            })
            ->when($request->input('payment_status') === 'paid', function ($query) {
                $query->where('is_paid', 1);
            })
            ->filterByZoneIds($request['zone_ids'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
            ->filterByDateRange($request['start_date'], $request['end_date'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        foreach ($bookings as $booking) {
            if ($booking->repeat->isNotEmpty()) {
                $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                    $parts = explode('-', $repeat->readable_id);
                    $suffix = end($parts);
                    return $this->readableIdToNumber($suffix);
                });

                $booking->repeats = $sortedRepeats->values();

                $nextService = $booking->repeats->firstWhere('booking_status', 'ongoing')
                    ?? $booking->repeats->firstWhere('booking_status', 'accepted')
                    ?? $booking->repeats->firstWhere('booking_status', 'pending');

                $lastRepeat = $booking->repeats->last();
                $booking['nextServiceId'] = $nextService ? $nextService->id : null;
                $booking['nextService'] = $nextService;
                $booking['lastRepeat'] = $lastRepeat;
            }
        }


        $zones = $this->zone->withoutGlobalScope('translate')->select('id', 'name')->get();
        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $subCategories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::admin.booking.list', compact('bookings', 'zones', 'categories', 'subCategories', 'queryParams', 'filterCounter', 'statusCounts'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function checkBooking(): Renderable
    {
        $this->booking->where('is_checked', 0)->update(['is_checked' => 1]);
    }

    public function details($id, Request $request): Renderable|RedirectResponse
    {
        $this->authorize('booking_view');
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status']);
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        if ($request->web_page == 'details') {

            $booking = $this->booking->with(['zone' , 'detail.service' => function ($query) {
                $query->withTrashed();
            }, 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider', 'serviceman', 'status_histories.user', 'booking_details_amounts', 'team.servicemen.user', 'attendances.serviceman.user', 'supervisorSeenBy'])
                ->find($id);

            $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();

            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category?->id)->where('sub_category_id', $subCategory?->id)->get();

            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $allProviders = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                    $query->where('is_suspended', 0);
                })
                ->where('service_availability', 1)
                ->withCount('reviews')
                ->ofApproval(1)->ofStatus(1)
                ->whereNot('id', $booking->provider_id)
                ->get();

            $providers = [];

            foreach ($allProviders as $provider) {
                $serviceLocation = getProviderSettings(providerId: $provider->id, key: 'service_location', type: 'provider_config');

                if (in_array($booking->service_location, $serviceLocation)) {
                    $providers[] = $provider;
                }
            }

            $currentlyAssignProvider = $booking->provider_id
                ? $this->provider->withCount('bookings', 'reviews')->find($booking->provider_id)
                : null;

            $sort_by = 'default';
            $zoneCenter = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($booking->zone_id);

            $currentZone = [];
            $centerLat = [];
            $centerLng = [];
            $area = [];

            if (isset($zoneCenter)) {
                $currentZone = format_coordinates(json_decode($zoneCenter->coordinates[0]->toJson(), true));
                $centerLat = trim(explode(' ', $zoneCenter->center)[1], 'POINT()');
                $centerLng = trim(explode(' ', $zoneCenter->center)[0], 'POINT()');

                $area = json_decode($zoneCenter->coordinates[0]->toJson(), true);
            }

            $teams = $booking?->provider_id
                ? SupervisorTeam::with('servicemen.user')
                    ->where('provider_id', $booking->provider_id)
                    ->where('is_active', 1)
                    ->latest()
                    ->get()
                : collect();

            return view('bookingmodule::admin.booking.details', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area', 'booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by', 'currentlyAssignProvider', 'teams'));
        } elseif ($request->web_page == 'status') {
            $booking = $this->booking->with(['detail.service', 'customer', 'provider', 'service_address', 'serviceman.user', 'service_address', 'status_histories.user'])->find($id);

            $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();
            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();
            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $allProviders = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->ofApproval(1)->ofStatus(1)
                ->whereNot('id', $booking->provider_id)
                ->get();

            $providers = [];

            foreach ($allProviders as $provider) {
                $serviceLocation = getProviderSettings(providerId: $provider->id, key: 'service_location', type: 'provider_config');

                if (in_array($booking->service_location, $serviceLocation)) {
                    $providers[] = $provider;
                }
            }

            $currentlyAssignProvider = $booking->provider_id
                ? $this->provider->withCount('bookings', 'reviews')->find($booking->provider_id)
                : null;

            $sort_by = 'default';
            return view('bookingmodule::admin.booking.status', compact('booking', 'webPage', 'servicemen', 'customerAddress', 'category', 'subCategory', 'services', 'providers', 'zones', 'sort_by', 'currentlyAssignProvider'));
        }

        Toastr::success(translate(ACCESS_DENIED['key']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable|RedirectResponse
     * @throws AuthorizationException
     */
    public function repeatDetails($id, Request $request): Renderable|RedirectResponse
    {
        $this->authorize('booking_view');
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,service_log']);
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        $booking = $this->booking->with(['repeat.detail.service','repeat.scheduleHistories','repeat.repeatHistories', 'detail.service' => function ($query) {
            $query->withTrashed();
        }, 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider',
            'serviceman', 'status_histories.user'])
            ->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        $servicemen = $this->serviceman->with(['user'])
            ->where('provider_id', $booking?->provider_id)
            ->whereHas('user', function ($query) {
                $query->ofStatus(1);
            })
            ->latest()
            ->get();

        $category = $booking?->detail?->first()?->service?->category;
        $subCategory = $booking?->detail?->first()?->service?->subCategory;
        $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

        $customerAddress = $this->userAddress->find($booking['service_address_id']);
        $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

        $providers = $this->provider
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                    $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                });
            })
            ->where('zone_id', $booking->zone_id)
            ->withCount('bookings', 'reviews')
            ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                $query->where('is_suspended', 0);
            })
            ->where('service_availability', 1)
            ->withCount('reviews')
            ->ofApproval(1)->ofStatus(1)->get();

        $sort_by = 'default';
        $id = "325778a8-53bd-4de5-a6bb-826f62edf603";
        $zoneCenter = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($id);

        $currentZone = [];
        $centerLat = [];
        $centerLng = [];
        $area = [];

        if (isset($zoneCenter)) {
            $currentZone = format_coordinates(json_decode($zoneCenter->coordinates[0]->toJson(), true));
            $centerLat = trim(explode(' ', $zoneCenter->center)[1], 'POINT()');
            $centerLng = trim(explode(' ', $zoneCenter->center)[0], 'POINT()');

            $area = json_decode($zoneCenter->coordinates[0]->toJson(), true);
        }

        if ($booking->repeat->isNotEmpty()) {
            $repeatHistoryCollection = $booking->repeat->flatMap(function ($repeat) {
                return $repeat->repeatHistories->map(function ($history) {
                    $history->log_details = json_decode($history->log_details);
                    return $history;
                });
            });

            $booking['repeatHistory'] = $repeatHistoryCollection->toArray();
            $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                $parts = explode('-', $repeat->readable_id);
                $suffix = end($parts);
                return $this->readableIdToNumber($suffix);
            });
            $booking['repeats'] = $sortedRepeats->values()->toArray();

            $nextService = collect($booking['repeats'])->firstWhere('booking_status', 'ongoing');
            if (!$nextService) {
                $nextService = collect($booking['repeats'])->firstWhere('booking_status', 'accepted');
            }
            if (!$nextService) {
                $nextService = collect($booking['repeats'])->firstWhere('booking_status', 'pending');
            }

            $serviceSchedules = collect($booking['repeats'])->pluck('service_schedule')->flatten()->map(function ($schedule) {
                return Carbon::parse($schedule);
            });

            $booking['completeCancel'] = collect($booking['repeats'])->filter(function ($repeat) {
                return in_array($repeat['booking_status'], ['completed', 'canceled']);
            })->values()->toArray();

            $booking['upComing'] = collect($booking['repeats'])->filter(function ($repeat) use ($nextService) {

                if ($repeat['booking_status'] === 'pending') {
                    return in_array($repeat['booking_status'], ['accepted', 'pending']);
                }

                return in_array($repeat['booking_status'], ['accepted', 'pending']) && $repeat['readable_id'] !== $nextService['readable_id'];
            })->values()->toArray();


            $booking['nextService'] = $nextService;
            $booking['time'] = $serviceSchedules->max()->format('g:ia');
            $booking['startDate'] = $serviceSchedules->min()->format('d M, Y');
            $booking['endDate'] = $serviceSchedules->max()->format('d M, Y');
            $booking['totalCount'] = count($booking['repeats']);
            $booking['bookingType'] = $booking['repeats'][0]['booking_type'];

            if ($booking['bookingType'] == 'weekly') {
                $dayOrder = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

                $booking['weekNames'] = collect($booking['repeats'])
                    ->pluck('service_schedule')
                    ->map(function ($schedule) {
                        return \Carbon\Carbon::parse($schedule)->format('l');
                    })
                    ->unique()
                    ->sort(function ($a, $b) use ($dayOrder) {
                        return array_search($a, $dayOrder) - array_search($b, $dayOrder);
                    })
                    ->values()
                    ->toArray();
            }

            $booking['completedCount'] = collect($booking['repeats'])->where('booking_status', 'completed')->count();
            $booking['canceledCount'] = collect($booking['repeats'])->where('booking_status', 'canceled')->count();

            $booking['repeats'] = array_map(function ($repeat) {
                if (isset($repeat['repeat_histories'])) {
                    unset($repeat['repeat_histories']);
                }
                return $repeat;
            }, $booking['repeats']);
        }

        if ($webPage == 'details') {
            return view('bookingmodule::admin.booking.repeat-booking-details', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area', 'booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by'));

        }elseif ($webPage == 'service_log'){
            return view('bookingmodule::admin.booking.service-log', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area', 'booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by'));

        }

        Toastr::success(translate(ACCESS_DENIED['key']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable|RedirectResponse
     * @throws AuthorizationException
     */
    public function repeatSingleDetails($id, Request $request): Renderable|RedirectResponse
    {
        $this->authorize('booking_view');
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status']);
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        $booking = $this->bookingRepeat->with(['booking.category', 'booking.subCategory', 'detail.service' => function ($query) {
            $query->withTrashed();
        }, 'detail.service', 'scheduleHistories.user', 'statusHistories.user', 'booking.service_address', 'booking.customer', 'booking.provider', 'serviceman.user'])
            ->find($id);

        if (!$booking) {
            Toastr::error(translate('Booking not found'));
            return back();
        }

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking?->booking?->service_address;
        unset($booking->service_address_location);

        $servicemen = $this->serviceman->with(['user'])
            ->where('provider_id', $booking?->provider_id)
            ->whereHas('user', function ($query) {
                $query->ofStatus(1);
            })
            ->latest()
            ->get();

        $category = $booking?->detail?->first()?->service?->category;
        $subCategory = $booking?->detail?->first()?->service?->subCategory;
        $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

        $customerAddress = $this->userAddress->find($booking['service_address_id']);
        $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

        $providers = $this->provider
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                            ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when(isset($booking->booking->sub_category_id), function ($query) use ($request, $booking) {
                $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                    $query->where('sub_category_id', $booking->booking->sub_category_id)->where('is_subscribed', 1);
                });
            })
            ->where('zone_id', $booking->booking->zone_id)
            ->withCount('bookings', 'reviews')
            ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                $query->where('is_suspended', 0);
            })
            ->where('service_availability', 1)
            ->withCount('reviews')
            ->ofApproval(1)->ofStatus(1)->get();

        $sort_by = 'default';
        $id = "325778a8-53bd-4de5-a6bb-826f62edf603";
        $zoneCenter = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($id);

        $currentZone = [];
        $centerLat = [];
        $centerLng = [];
        $area = [];

        if (isset($zoneCenter)) {
            $currentZone = format_coordinates(json_decode($zoneCenter->coordinates[0]->toJson(), true));
            $centerLat = trim(explode(' ', $zoneCenter->center)[1], 'POINT()');
            $centerLng = trim(explode(' ', $zoneCenter->center)[0], 'POINT()');

            $area = json_decode($zoneCenter->coordinates[0]->toJson(), true);
        }
        if ($request->web_page == 'details') {
            return view('bookingmodule::admin.booking.rebooking-ongoing', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area', 'booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by'));

        }elseif ($request->web_page == 'status') {
            return view('bookingmodule::admin.booking.repeat-status', compact('booking', 'webPage', 'servicemen', 'customerAddress', 'category', 'subCategory', 'services', 'providers', 'zones', 'sort_by'));
        }
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        $validated = $request->validate([
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key'))]);

        $booking = $this->booking->find($bookingId);
        $repeatBooking = $this->bookingRepeat->find($bookingId);

        if ($booking) {
            if($booking->booking_status == 'ongoing' && $request['booking_status'] == 'canceled'){
                return response()->json(response_formatter(BOOKING_ALREADY_ONGOING, errors: [['error_code' => 422, 'message' => BOOKING_ALREADY_ONGOING['message']]]), 422);
            }
            return $this->updateBookingStatus($booking, $validated['booking_status'], $request);
        }

        if ($repeatBooking) {
            return $this->updateRepeatBookingStatus($repeatBooking, $validated['booking_status'], $request);
        }

        return response()->json(response_formatter(DEFAULT_204), 204);
    }

    private function updateBookingStatus($booking, string $status, Request $request): JsonResponse
    {
        $booking->booking_status = $status;

        if ($booking->isDirty('booking_status')) {
            DB::transaction(function () use ($booking, $status, $request) {
                if ($booking->repeat) {
                    foreach ($booking->repeat->whereIn('booking_status', ['pending', 'accepted', 'ongoing']) as $repeat) {
                        $repeat->update([
                            'provider_id' => $request->provider_id,
                            'booking_status' => $status,
                            'serviceman_id' => null]);

                        $this->logBookingStatusHistory($repeat->id, $status, $request->user()->id, $booking->id);
                    }

                    if ($status == 'canceled' && $booking->repeat->contains('booking_status', 'completed')) {
                        $booking->booking_status = 'completed';
                    }
                }

                $booking->save();
                $this->logBookingStatusHistory(null, $status, $request->user()->id, $booking->id);
            });

            return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
        }

        return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
    }

    private function updateRepeatBookingStatus($repeatBooking, string $status, Request $request): JsonResponse
    {
        $repeatBooking->booking_status = $status;

        if ($status == 'canceled' && $repeatBooking->extra_fee > 0){

            $booking = $this->booking->where('id', $repeatBooking->booking_id)->first();
            $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                $parts = explode('-', $repeat->readable_id);
                $suffix = end($parts);
                return $this->readableIdToNumber($suffix);
            });

            $booking['repeats'] = $sortedRepeats->values()->toArray();

            $nextService = collect($booking['repeats'])
                ->where('booking_status', 'ongoing')
                ->skip(1)
                ->first();

            if (!$nextService) {
                $nextService = collect($booking['repeats'])
                    ->where('booking_status', 'accepted')
                    ->skip(1)
                    ->first();
            }

            if (!$nextService) {
                $nextService = collect($booking['repeats'])
                    ->where('booking_status', 'pending')
                    ->skip(1)
                    ->first();
            }

            if (isset($nextService)) {
                $nextServiceId = $nextService['id'];
                $nextServiceFee = $this->bookingRepeat->where('id', $nextServiceId)->first();
                $nextServiceFee->extra_fee = $repeatBooking->extra_fee;
                $nextServiceFee->total_booking_amount += $repeatBooking->extra_fee;
                $nextServiceFee->save();
            }

            $repeatBooking->total_booking_amount -= $repeatBooking->extra_fee;
            $repeatBooking->extra_fee = 0;
        }

        if ($repeatBooking->isDirty('booking_status')) {
            DB::transaction(function () use ($repeatBooking, $status, $request) {

                $repeatBooking->save();
                $this->logBookingStatusHistory($repeatBooking->id, $status, $request->user()->id, $repeatBooking->booking_id);

                $relatedRepeats = $this->bookingRepeat->where('booking_id', $repeatBooking->booking_id)->get();
                if ($relatedRepeats->every(fn($repeat) => !in_array($repeat->booking_status, ['pending', 'accepted', 'ongoing']))) {
                    $repeatBooking->booking->update(['booking_status' => 'completed', 'is_paid' => 1]);
                }

                if (in_array($repeatBooking->booking_status, ['ongoing', 'completed', 'canceled'])) {
                    if ($repeatBooking->booking->booking_status != 'ongoing' && $repeatBooking->booking->booking_status != 'completed' && $repeatBooking->booking->booking_status != 'canceled') {
                        $repeatBooking->booking->booking_status = 'ongoing';
                        $repeatBooking->booking->save();
                    }
                }
            });

            return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
        }

        return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
    }

    private function logBookingStatusHistory(?string $repeatId, string $status, string $changedBy, string $bookingId): void
    {
        $this->bookingStatusHistory->create([
            'booking_id' => $bookingId,
            'booking_repeat_id' => $repeatId,
            'changed_by' => $changedBy,
            'booking_status' => $status]);
    }


    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function upComingBookingCancel($bookingId, Request $request): RedirectResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key'))]);

        $repeatBooking = $this->bookingRepeat->where('id', $bookingId)->first();
        if (isset($repeatBooking)){
            $repeatBooking->booking_status = $request['booking_status'];

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $bookingId;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];
            $bookingStatusHistory->booking_repeat_id = $repeatBooking->id;

            if ($repeatBooking->isDirty('booking_status')) {
                DB::transaction(function () use ($bookingStatusHistory, $repeatBooking) {
                    $repeatBooking->save();
                    $bookingStatusHistory->save();
                });

                Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['key']));
                return back();
            }
            Toastr::success(translate(NO_CHANGES_FOUND['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
        return back();
    }

    public function paymentUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'payment_status' => 'required|in:1']);

        $booking = $this->booking->where('id', $bookingId)->first();

        $repeatBooking = $this->bookingRepeat->where('id', $bookingId)->first();
        if (isset($booking)) {
            $booking->is_paid = 1; // unpaid status removed

            if ($booking->isDirty('is_paid')) {
                $booking->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        if (isset($repeatBooking)) {
            $repeatBooking->is_paid = 1; // unpaid status removed

            if ($repeatBooking->isDirty('is_paid')) {
                $repeatBooking->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function scheduleUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'service_schedule' => 'required']);

        $booking = $this->booking->where('id', $bookingId)->first();
        $bookingRepeat = $this->bookingRepeat->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->service_schedule = Carbon::parse($request->service_schedule)->toDateTimeString();

            $bookingScheduleHistory = $this->bookingScheduleHistory;
            $bookingScheduleHistory->booking_id = $bookingId;
            $bookingScheduleHistory->changed_by = $request->user()->id;
            $bookingScheduleHistory->schedule = $request['service_schedule'];

            if ($booking->isDirty('service_schedule')) {
                $booking->save();
                $bookingScheduleHistory->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }

        if (isset($bookingRepeat)) {
            $bookingRepeat->service_schedule = Carbon::parse($request->service_schedule)->toDateTimeString();

            $bookingRepeatScheduleHistory = $this->bookingScheduleHistory;
            $bookingRepeatScheduleHistory->booking_id = $bookingRepeat->booking_id;
            $bookingRepeatScheduleHistory->changed_by = $request->user()->id;
            $bookingRepeatScheduleHistory->schedule = $request['service_schedule'];
            $bookingRepeatScheduleHistory->booking_repeat_id = $bookingId;

            if ($bookingRepeat->isDirty('service_schedule')) {
                $bookingRepeat->save();
                $bookingRepeatScheduleHistory->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }
            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function upComingBookingScheduleUpdate($bookingId, Request $request): RedirectResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'service_schedule' => 'required']);

        $bookingRepeat = $this->bookingRepeat->where('id', $bookingId)->first();

        if (isset($bookingRepeat)) {
            $bookingRepeat->service_schedule = Carbon::parse($request->service_schedule)->toDateTimeString();

            $bookingRepeatScheduleHistory = $this->bookingScheduleHistory;
            $bookingRepeatScheduleHistory->booking_id = $bookingRepeat->booking_id;
            $bookingRepeatScheduleHistory->changed_by = $request->user()->id;
            $bookingRepeatScheduleHistory->schedule = $request['service_schedule'];
            $bookingRepeatScheduleHistory->booking_repeat_id = $bookingId;

            if ($bookingRepeat->isDirty('service_schedule')) {
                $bookingRepeat->save();
                $bookingRepeatScheduleHistory->save();

                Toastr::success(translate(DEFAULT_UPDATE_200['key']));
                return back();
            }
            Toastr::success(translate(NO_CHANGES_FOUND['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function providerUpdate($bookingId, Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        Validator::make($request->all(), [
            'provider_id' => 'required|uuid']);

        $booking = $this->booking->where('id', $bookingId)->first();

        if (isset($booking)) {
            $booking->provider_id = $request->provider_id;

            if ($booking->isDirty('provider_id')) {
                $booking->booking_status = 'accepted';
                $booking->serviceman_id = null;
                $booking->assigned_by = 'admin';

                if (!is_null($booking->repeat)) {
                    foreach ($booking->repeat->whereIn('booking_status', ['pending', 'accepted', 'ongoing']) as $bookingRepeat) {
                        $bookingRepeat->provider_id = $request->provider_id;
                        $bookingRepeat->booking_status = 'accepted';
                        $bookingRepeat->serviceman_id = null;
                        $bookingRepeat->save();
                    }
                }

                $booking->save();
                return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
            }

            return response()->json(response_formatter(NO_CHANGES_FOUND), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $bookingId
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function servicemanUpdate(Request $request): JsonResponse
    {
        $this->authorize('booking_can_manage_status');

        $booking = $this->booking->where('id', $request->booking_id)->first();
        $bookingRepeat = $this->bookingRepeat->where('id', $request->booking_id)->with('booking')->first();

        // Search-only refresh of the modal list (no assignment yet).
        $isAssigning = $request->filled('serviceman_id');

        if (isset($booking)) {
            if ($isAssigning && !bookingCanReassignCrew($booking)) {
                return response()->json(response_formatter(DEFAULT_400, null, [[
                    'message' => translate('Cannot change serviceman after the booking is on the way')
                ]]), 400);
            }

            if ($isAssigning) {
                $booking->serviceman_id = $request->serviceman_id;
                $booking->save();

                if (!is_null($booking->repeat)) {
                    foreach ($booking->repeat->whereIn('booking_status', ['pending', 'accepted', 'ongoing']) as $bookingRepeatItem) {
                        $bookingRepeatItem->serviceman_id = $request->serviceman_id;
                        $bookingRepeatItem->save();
                    }
                }
            }

            $search = $request->search;
            $servicemen = $this->serviceman
                ->where('provider_id', $booking?->provider_id)
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhereHas('user', function ($query) use ($key) {
                                $query->where('first_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $key . '%')
                                    ->orWhere('email', 'LIKE', '%' . $key . '%');
                            });
                        }
                    });
                })
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })->get();

            return response()->json([
                'view' => view('bookingmodule::admin.booking.partials.details.serviceman-info-modal-data', compact('servicemen', 'booking', 'search'))->render()
            ]);
        }
        if (isset($bookingRepeat)) {

            $parentBooking = $bookingRepeat->booking;
            if ($isAssigning && !bookingCanReassignCrew($parentBooking ?? $bookingRepeat)) {
                return response()->json(response_formatter(DEFAULT_400, null, [[
                    'message' => translate('Cannot change serviceman after the booking is on the way')
                ]]), 400);
            }

            if ($isAssigning) {
                $bookingRepeat->serviceman_id = $request->serviceman_id;
                $bookingRepeat->save();

                if ($bookingRepeat->booking) {
                    $bookingRepeat->booking->serviceman_id = $request->serviceman_id;
                    $bookingRepeat->booking->save();
                }
            }

            $search = $request->search;
            $servicemen = $this->serviceman
                ->where('provider_id', $bookingRepeat?->provider_id)
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhereHas('user', function ($query) use ($key) {
                                $query->where('first_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $key . '%')
                                    ->orWhere('email', 'LIKE', '%' . $key . '%');
                            });
                        }
                    });
                })
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })->get();

            $booking = $bookingRepeat;

            return response()->json([
                'view' => view('bookingmodule::admin.booking.partials.details.serviceman-info-modal-data', compact('servicemen', 'booking', 'search'))->render()
            ]);
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param $service_address_id
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function serviceAddressUpdate($service_address_id, Request $request): RedirectResponse
    {
        $this->authorize('booking_edit');

        Validator::make($request->all(), [
            'city' => 'required',
            'street' => 'required',
            'zip_code' => 'required',
            'country' => 'required',
            'address' => 'required',
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address_label' => 'required',
            'zone_id' => 'required|uuid',
            'latitude' => 'required',
            'longitude' => 'required']);

        $point = new Point($request->latitude, $request->longitude);
        $zoneExists = $this->zone->where('id', $request->zone_id)->whereContains('coordinates', $point)->exists();
        if (!$zoneExists) {
            Toastr::error(translate('coordinates_must_be_within_the_selected_zone'));
            return back()->withInput();
        }

        $userAddress = $this->userAddress->find($service_address_id);
        $userAddress->city = $request['city'];
        $userAddress->street = $request['street'];
        $userAddress->zip_code = $request['zip_code'];
        $userAddress->country = $request['country'];
        $userAddress->address = $request['address'];
        $userAddress->contact_person_name = $request['contact_person_name'];
        $userAddress->contact_person_number = $request['contact_person_number'];
        $userAddress->address_label = $request['address_label'];
        $userAddress->zone_id = $request['zone_id'];
        $userAddress->lat = $request['latitude'];
        $userAddress->lon = $request['longitude'];
        $userAddress->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws AuthorizationException
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\InvalidArgumentException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Writer\Exception\WriterNotOpenedException
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('booking_view');
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all']);

        $allowedStatuses = ['accepted', 'ongoing', 'completed', 'canceled', 'all'];
        $bookingStatus = $request->input('booking_status', 'accepted');
        if (!in_array($bookingStatus, $allowedStatuses, true)) {
            $bookingStatus = 'accepted';
        }

        $maxBookingAmount = (business_config('max_booking_amount', 'booking_setup'))->live_values;
        $items = $this->booking
            ->with(['customer', 'provider', 'repeat'])
            ->search($request['search'], ['readable_id'])
            ->when($bookingStatus != 'all', function ($query) use ($bookingStatus, $maxBookingAmount) {
                $query->when($bookingStatus == 'accepted', function ($query) use ($maxBookingAmount) {
                    $query->adminAcceptedBookings($maxBookingAmount);
                })->ofBookingStatus($bookingStatus);
            })
            ->when($request['service_type'] != 'all', function ($query) use ($request) {
                return $query->ofRepeatBookingStatus($request['service_type'] === 'repeat' ? 1 : ($request['service_type'] === 'regular' ? 0 : null));
            })
            ->when($request['provider_assigned'] == 'assigned', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNotNull('provider_id')
                        ->orWhereHas('repeat', function ($q) {
                            $q->whereNotNull('provider_id');
                        });
                });
            })
            ->when($request['provider_assigned'] == 'unassigned', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('provider_id')
                        ->orWhereDoesntHave('repeat', function ($q) {
                            $q->whereNotNull('provider_id');
                        });
                });
            })
            ->filterByZoneIds($request['zone_ids'])
            ->filterBySubcategoryIds($request['sub_category_ids'])
            ->filterByCategoryIds($request['category_ids'])
            ->filterByDateRange($request['start_date'], $request['end_date'])
            ->latest()->get();

        $formatted = $items->map(function ($item, $key) {
            $serviceAddress = is_string($item->service_address) ? json_decode($item->service_address) : $item->service_address;
            if ($item->is_repeated) {
                $sortedRepeats = $item->repeat->sortBy(function ($repeat) {
                    $parts = explode('-', $repeat->readable_id);
                    return $this->readableIdToNumber(end($parts));
                })->values();

                $nextService = $sortedRepeats->firstWhere('booking_status', 'ongoing')
                    ?? $sortedRepeats->firstWhere('booking_status', 'accepted')
                    ?? $sortedRepeats->firstWhere('booking_status', 'pending');
                $lastRepeat = $sortedRepeats->last();

                $scheduleSource = $nextService?->service_schedule ?? $lastRepeat?->service_schedule;
            } else {
                $scheduleSource = $item->service_schedule;
            }

            return [
                translate('SL') => $key + 1,
                translate('Booking_ID') => $item->readable_id,
                translate('Booking_Date') => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-M-Y h:i A') : '',
                translate('Where_Service_will_be_Provided') => $item->service_location == 'provider' ? translate('Provider Location') : translate('Customer Location'),
                translate('Schedule_Date') => $scheduleSource ? \Carbon\Carbon::parse($scheduleSource)->format('d-M-Y h:i A') : '',
                translate('Customer_Name') => $item->customer ? trim(($item->customer->first_name ?? '') . ' ' . ($item->customer->last_name ?? '')) : ($serviceAddress?->contact_person_name ?? ''),
                translate('Customer_Phone') => $item->customer ? $item->customer->phone : ($serviceAddress?->contact_person_number ?? ''),
                translate('Provider_Name') => $item->provider?->company_name ?? translate('unassigned'),
                translate('Provider_Phone') => $item->provider?->company_phone ?? '',
                translate('Total_Amount') => with_currency_symbol($item->total_booking_amount),
                translate('Payment_Status') => translate('paid')];
        });

        $fileName = 'bookings_' . $bookingStatus . '_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function invoice($id, Request $request): Renderable
    {
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function fullBookingInvoice($id): Renderable
    {
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user','repeat'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function fullBookingSingleInvoice($id): Renderable
    {
        $booking = $this->bookingRepeat->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'booking', 'provider', 'serviceman'])->find($id);

        $booking->booking->service_address = $booking->booking->service_address_location != null ? json_decode($booking->booking->service_address_location) : $booking->booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-single-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function customerFullBookingInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user','repeat'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function customerFullBookingSingleInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->bookingRepeat->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'booking', 'provider', 'serviceman'])->find($id);

        $booking->booking->service_address = $booking->booking->service_address_location != null ? json_decode($booking->booking->service_address_location) : $booking->booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-single-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function providerFullBookingInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user','repeat'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function providerFullBookingSingleInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->bookingRepeat->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'booking', 'provider', 'serviceman'])->find($id);

        $booking->booking->service_address = $booking->booking->service_address_location != null ? json_decode($booking->booking->service_address_location) : $booking->booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-single-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param $lang
     * @return Renderable
     */
    public function servicemanFullBookingSingleInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->bookingRepeat->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'booking', 'provider', 'serviceman'])->find($id);

        $booking->booking->service_address = $booking->booking->service_address_location != null ? json_decode($booking->booking->service_address_location) : $booking->booking->service_address;

        return view('bookingmodule::admin.booking.fullbooking-single-invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function customerInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function providerInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function servicemanInvoice($id, $lang): Renderable
    {
        App::setLocale($lang);
        $booking = $this->booking->with(['detail.service' => function ($query) {
            $query->withTrashed();
        }, 'customer', 'provider', 'serviceman', 'status_histories.user'])->find($id);

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetVariant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $variations = Variation::where('service_id', $request['service_id'])
            ->where('zone_id', $request['zone_id'])
            ->where('price', '>', 0)
            ->get();
        return response()->json(response_formatter(DEFAULT_200, $variations, null), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetServiceInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_id' => 'required|uuid',
            'variant_key' => 'required',
            'quantity' => 'required|numeric']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $service = Service::active()
            ->with(['category.category_discount', 'category.campaign_discount', 'service_discount'])
            ->where('id', $request['service_id'])
            ->with(['variations' => fn($query) => $query->where('variant_key', $request['variant_key'])->where('zone_id', $request['zone_id'])])
            ->first();

        $quantity = $request['quantity'];
        $variation_price = $service?->variations[0]?->price;

        $basic_discount = basic_discount_calculation($service, $variation_price * $quantity);
        $campaign_discount = campaign_discount_calculation($service, $variation_price * $quantity);
        $subtotal = round($variation_price * $quantity, 2);

        $applicable_discount = ($campaign_discount >= $basic_discount) ? $campaign_discount : $basic_discount;

        $tax = round((($variation_price * $quantity - $applicable_discount) * 0) / 100, 2);

        $basic_discount = $basic_discount > $campaign_discount ? $basic_discount : 0;
        $campaign_discount = $campaign_discount >= $basic_discount ? $campaign_discount : 0;
        $discountType = $basic_discount > 0 ? 'service' : ($campaign_discount > 0 ? 'campaign' : '');

        $data = collect([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'variant_key' => $service?->variations[0]?->variant_key,
            'quantity' => $request['quantity'],
            'service_cost' => $variation_price,
            'total_discount_amount' => $basic_discount + $campaign_discount,
            'discount_type' => $discountType,
            'coupon_code' => null,
            'tax_amount' => round($tax, 2),
            'total_cost' => round($subtotal - $basic_discount - $campaign_discount + $tax, 2),
            'zone_id' => $request['zone_id']
        ]);

        return response()->json([
            'view' => view('bookingmodule::admin.booking.partials.details.table-row', compact('data'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function updateBookingService(Request $request): RedirectResponse
    {

        $this->authorize('booking_edit');

        Validator::make($request->all(), [
            'qty' => 'required|array',
            'qty.*' => 'int',
            'service_ids' => 'required|array',
            'service_ids.*' => 'uuid',
            'variant_keys' => 'required|array',
            'variant_keys.*' => 'string',
            'zone_id' => 'required|uuid',
            'booking_id' => 'required|uuid'])->validate();

        $service_info = [];
        foreach ($request['service_ids'] as $key => $service_id) {
            $variant_key = $request['variant_keys'][$key] ?? null;
            $quantity = $request['qty'][$key] ?? 0;

            $service_info[] = [
                'service_id' => $service_id,
                'variant_key' => $variant_key,
                'quantity' => $quantity];
        }
        $request->merge(['service_info' => collect($service_info)]);

        $existing_services = $this->bookingDetails->where('booking_id', $request['booking_id'])->get();
        foreach ($existing_services as $item) {
            if (!$request['service_info']->where('service_id', $item->service_id)->where('variant_key', $item->variant_key)->first()) {
                $request['service_info']->push([
                    'service_id' => $item->service_id,
                    'variant_key' => $item->variant_key,
                    'quantity' => 0]);
            }
        }

        foreach ($request['service_info'] as $key => $item) {
            $existing_service = $this->bookingDetails
                ->where('booking_id', $request['booking_id'])
                ->where('service_id', $item['service_id'])
                ->where('variant_key', $item['variant_key'])
                ->first();

            if (!$existing_service) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];
                $this->addNewBookingService($request);
            } else if ($existing_service && $item['quantity'] == 0) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['quantity'] = $item['quantity'];

                $this->remove_service_from_booking($request);
            } else if ($existing_service && $existing_service->quantity < $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existing_service->quantity;
                $request['new_quantity'] = (int)$item['quantity'];
                $this->increase_service_quantity_from_booking($request);
            } else if ($existing_service && $existing_service->quantity > $item['quantity']) {
                $request['service_id'] = $item['service_id'];
                $request['variant_key'] = $item['variant_key'];
                $request['old_quantity'] = $existing_service->quantity;
                $request['new_quantity'] = (int)$item['quantity'];

                $this->decrease_service_quantity_from_booking($request);
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function updateRepeatBookingService(Request $request): RedirectResponse
    {
        $this->authorize('booking_edit');

        Validator::make($request->all(), [
            'qty' => 'required|array',
            'qty.*' => 'int',
            'service_ids' => 'required|array',
            'service_ids.*' => 'uuid',
            'variant_keys' => 'required|array',
            'variant_keys.*' => 'string',
            'zone_id' => 'required|uuid',
            'booking_id' => 'required|uuid'])->validate();

        $service_info = [];
        foreach ($request['service_ids'] as $key => $service_id) {
            $variant_key = $request['variant_keys'][$key] ?? null;
            $quantity = $request['qty'][$key] ?? 0;

            $service_info[] = [
                'service_id' => $service_id,
                'variant_key' => $variant_key,
                'quantity' => $quantity];
        }
        $request->merge(['service_info' => collect($service_info)]);
        $booking = $this->bookingRepeat
            ->with('detail')
            ->where('id', $request['booking_repeat_id'])->first();

        $totalOldQuantity = 0;
        $totalNewQuantity = 0;
        $updateQuantity = [];

        foreach ($request['service_info'] as $key => $item) {
            $existingService = $this->bookingRepeatDetail
                ->where('booking_repeat_id', $request['booking_repeat_id'])
                ->where('service_id', $item['service_id'])
                ->where('variant_key', $item['variant_key'])
                ->first();

            if ($existingService) {
                $totalOldQuantity += $existingService->quantity;

                if ($existingService->quantity < $item['quantity']) {
                    $request['service_id'] = $item['service_id'];
                    $request['variant_key'] = $item['variant_key'];
                    $request['old_quantity'] = $existingService->quantity;
                    $request['new_quantity'] = (int)$item['quantity'];

                    $this->increase_service_quantity_from_booking_repeat($request);
                } else if ($existingService->quantity > $item['quantity']) {
                    $request['service_id'] = $item['service_id'];
                    $request['variant_key'] = $item['variant_key'];
                    $request['old_quantity'] = $existingService->quantity;
                    $request['new_quantity'] = (int)$item['quantity'];

                    $this->decrease_service_quantity_from_booking_repeat($request);
                }

                $totalNewQuantity += (int)$item['quantity'];
                $updateQuantity[] = [
                    'service_id' => $item['service_id'],
                    'quantity' => (int)$item['quantity'],
                    'variant_key' => $item['variant_key'],
                    'service_name' => $existingService->service_name,
                    'service_cost' => $existingService->service_cost];
            }
        }

        if ($request['next_all_booking_change'] == 1){
            $mainBooking = $this->booking->where('id', $request['booking_id'])->first();
            $sourceRepeatBooking = $this->bookingRepeat->where('id', $request['booking_repeat_id'])->first();
            $serviceFee = 0;

            if (Str::endsWith($sourceRepeatBooking->readable_id, '-A') && !Str::endsWith($sourceRepeatBooking->readable_id, '-AA')) {
                $serviceFee = $sourceRepeatBooking->extra_fee;
            }

            $targetRepeatBookings = $this->bookingRepeat
                ->where('booking_id', $request['booking_id'])
                ->whereIn('booking_status', ['accepted', 'ongoing'])
                ->where('id', '!=', $sourceRepeatBooking ? $sourceRepeatBooking->id : null)
                ->orderBy('readable_id')
                ->get();

            if ($sourceRepeatBooking) {
                $targetRepeatBookingsWithSource = new Collection($targetRepeatBookings->toArray());

                $targetRepeatBookingsWithSource->push($sourceRepeatBooking);
                $sortedReadableIds = $targetRepeatBookingsWithSource->pluck('readable_id')->sort()->values();
                $minReadableId = $sortedReadableIds->first();
                $maxReadableId = $sortedReadableIds->last();

                if ($totalOldQuantity != $totalNewQuantity) {
                    foreach ($updateQuantity as $key => $update) {
                        $existService = $this->bookingRepeatDetail
                            ->where('booking_repeat_id', $request['booking_repeat_id'])
                            ->where('service_id', $update['service_id'])
                            ->first();

                        if ($existService) {
                            $updateQuantity[$key]['discount_amount'] = $existService->discount_amount;
                            $updateQuantity[$key]['tax_amount'] = $existService->tax_amount;
                            $updateQuantity[$key]['total_cost'] = $existService->total_cost;
                            $updateQuantity[$key]['repeat_details_id'] = $existService->id;
                        }
                    }
                    $bookingRepeatHistory = $this->bookingRepeatHistory;
                    $bookingRepeatHistory->booking_id = $request['booking_id'];
                    $bookingRepeatHistory->booking_repeat_id = $request['booking_repeat_id'];
                    $bookingRepeatHistory->old_quantity = $totalOldQuantity;
                    $bookingRepeatHistory->new_quantity = $totalNewQuantity;
                    $bookingRepeatHistory->is_multiple = $request['next_all_booking_change'] ? 1 : 0;
                    $bookingRepeatHistory->readable_id = "$minReadableId - $maxReadableId";
                    $bookingRepeatHistory->log_details = json_encode($updateQuantity);
                    $bookingRepeatHistory->total_booking_amount = $sourceRepeatBooking->total_booking_amount - $serviceFee;
                    $bookingRepeatHistory->total_tax_amount = $sourceRepeatBooking->total_tax_amount;
                    $bookingRepeatHistory->total_discount_amount = $sourceRepeatBooking->total_discount_amount;
                    $bookingRepeatHistory->extra_fee = $sourceRepeatBooking->extra_fee;
                    $bookingRepeatHistory->save();
                }

                foreach ($targetRepeatBookings as $targetBooking) {
                    $targetBooking->total_booking_amount = $sourceRepeatBooking->total_booking_amount - $serviceFee;
                    $targetBooking->total_tax_amount = $sourceRepeatBooking->total_tax_amount;
                    $targetBooking->total_discount_amount = $sourceRepeatBooking->total_discount_amount;
                    $targetBooking->total_campaign_discount_amount = $sourceRepeatBooking->total_campaign_discount_amount;
                    $targetBooking->save();
                }

                foreach ($sourceRepeatBooking->detail as $sourceDetail) {
                    foreach ($targetRepeatBookings as $targetBooking) {
                        foreach ($targetBooking->detail as $targetDetail) {
                            $targetDetail->quantity = $sourceDetail->quantity;
                            $targetDetail->tax_amount = $sourceDetail->tax_amount;
                            $targetDetail->total_cost = $sourceDetail->total_cost;
                            $targetDetail->discount_amount = $sourceDetail->discount_amount;
                            $targetDetail->campaign_discount_amount = $sourceDetail->campaign_discount_amount;
                            $targetDetail->overall_coupon_discount_amount = 0;
                            $targetDetail->save();
                        }
                    }
                }

                foreach ($sourceRepeatBooking->details_amounts as $sourceAmount) {
                    foreach ($targetRepeatBookings as $targetBooking) {
                        foreach ($targetBooking->details_amounts as $targetAmount) {
                            $targetAmount->service_quantity = $sourceAmount->service_quantity;
                            $targetAmount->service_tax = $sourceAmount->service_tax;
                            $targetAmount->coupon_discount_by_admin = 0;
                            $targetAmount->coupon_discount_by_provider = 0;
                            $targetAmount->discount_by_admin = $sourceAmount->discount_by_admin;
                            $targetAmount->discount_by_provider = $sourceAmount->discount_by_provider;
                            $targetAmount->campaign_discount_by_admin = $sourceAmount->campaign_discount_by_admin;
                            $targetAmount->campaign_discount_by_provider = $sourceAmount->campaign_discount_by_provider;
                            $targetAmount->save();
                        }
                    }
                }
            }


            $mainBooking->total_booking_amount = $targetRepeatBookings->sum('total_booking_amount') + $sourceRepeatBooking->total_booking_amount;
            $mainBooking->total_tax_amount = $targetRepeatBookings->sum('total_tax_amount') + $sourceRepeatBooking->total_tax_amount;
            $mainBooking->total_discount_amount = $targetRepeatBookings->sum('total_discount_amount') + $sourceRepeatBooking->total_discount_amount;
            $mainBooking->total_campaign_discount_amount = $targetRepeatBookings->sum('total_campaign_discount_amount') + $sourceRepeatBooking->total_campaign_discount_amount;
            $mainBooking->save();

        }else{
            $mainBooking = $this->booking->where('id', $request['booking_id'])->first();
            $sourceRepeatBooking = $this->bookingRepeat->where('id', $request['booking_repeat_id'])->first();
            $repeatBooking = $this->bookingRepeat->where('booking_id', $request['booking_id'])->get();

            $mainBooking->total_booking_amount = $repeatBooking->sum('total_booking_amount');
            $mainBooking->total_tax_amount = $repeatBooking->sum('total_tax_amount');
            $mainBooking->total_discount_amount = $repeatBooking->sum('total_discount_amount');
            $mainBooking->total_campaign_discount_amount = $repeatBooking->sum('total_campaign_discount_amount');
            $mainBooking->save();

            if ($totalOldQuantity != $totalNewQuantity) {
                foreach ($updateQuantity as $key => $update) {
                    $existService = $this->bookingRepeatDetail
                        ->where('booking_repeat_id', $request['booking_repeat_id'])
                        ->where('service_id', $update['service_id'])
                        ->first();

                    if ($existService) {
                        $updateQuantity[$key]['discount_amount'] = $existService->discount_amount;
                        $updateQuantity[$key]['tax_amount'] = $existService->tax_amount;
                        $updateQuantity[$key]['total_cost'] = $existService->total_cost;
                        $updateQuantity[$key]['repeat_details_id'] = $existService->id;
                    }
                }

                $bookingRepeatHistory = $this->bookingRepeatHistory;
                $bookingRepeatHistory->booking_id = $request['booking_id'];
                $bookingRepeatHistory->booking_repeat_id = $request['booking_repeat_id'];
                $bookingRepeatHistory->old_quantity = $totalOldQuantity;
                $bookingRepeatHistory->new_quantity = $totalNewQuantity;
                $bookingRepeatHistory->is_multiple = $request['next_all_booking_change'] ? 1 : 0;
                $bookingRepeatHistory->readable_id = $booking->readable_id;
                $bookingRepeatHistory->log_details = json_encode($updateQuantity);
                $bookingRepeatHistory->total_booking_amount = $sourceRepeatBooking->total_booking_amount;
                $bookingRepeatHistory->total_tax_amount = $sourceRepeatBooking->total_tax_amount;
                $bookingRepeatHistory->total_discount_amount = $sourceRepeatBooking->total_discount_amount;
                $bookingRepeatHistory->extra_fee = $sourceRepeatBooking->extra_fee;
                $bookingRepeatHistory->save();
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }


    public function reBookingDetails(Request $request, $id)
    {
        $this->authorize('booking_view');
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status']);
        $webPage = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        if ($request->web_page == 'details') {

            $booking = $this->booking->with(['detail.service' => function ($query) {
                $query->withTrashed();
            }, 'detail.service.category', 'detail.service.subCategory', 'detail.variation', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);

            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();

            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();

            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $providers = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                    $query->where('is_suspended', 0);
                })
                ->where('service_availability', 1)
                ->withCount('reviews')
                ->ofApproval(1)->ofStatus(1)->get();

            $sort_by = 'default';
            $id = "325778a8-53bd-4de5-a6bb-826f62edf603";
            $zoneCenter = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->find($id);

            $currentZone = [];
            $centerLat = [];
            $centerLng = [];
            $area = [];

            if (isset($zoneCenter)) {
                $currentZone = format_coordinates(json_decode($zoneCenter->coordinates[0]->toJson(), true));
                $centerLat = trim(explode(' ', $zoneCenter->center)[1], 'POINT()');
                $centerLng = trim(explode(' ', $zoneCenter->center)[0], 'POINT()');

                $area = json_decode($zoneCenter->coordinates[0]->toJson(), true);
            }

            return view('bookingmodule::admin.booking.rebooking-details', compact('zoneCenter', 'currentZone', 'centerLat', 'centerLng', 'area', 'booking', 'servicemen', 'webPage', 'customerAddress', 'services', 'zones', 'category', 'subCategory', 'providers', 'sort_by'));
        } elseif ($request->web_page == 'status') {
            $booking = $this->booking->with(['detail.service', 'customer', 'provider', 'service_address', 'serviceman.user', 'service_address', 'status_histories.user'])->find($id);
            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking?->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();
            $category = $booking?->detail?->first()?->service?->category;
            $subCategory = $booking?->detail?->first()?->service?->subCategory;
            $services = Service::select('id', 'name')->where('category_id', $category->id)->where('sub_category_id', $subCategory->id)->get();
            $customerAddress = $this->userAddress->find($booking['service_address_id']);
            $zones = Zone::ofStatus(1)->withoutGlobalScope('translate')->get();

            $providers = $this->provider
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    return $query->where(function ($query) use ($keys) {
                        foreach ($keys as $key) {
                            $query->orWhere('company_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_email', 'LIKE', '%' . $key . '%')
                                ->orWhere('company_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_name', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_phone', 'LIKE', '%' . $key . '%')
                                ->orWhere('contact_person_email', 'LIKE', '%' . $key . '%');
                        }
                    });
                })
                ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                    $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                        $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                    });
                })
                ->where('zone_id', $booking->zone_id)
                ->withCount('bookings', 'reviews')
                ->ofApproval(1)->ofStatus(1)->get();
            $sort_by = 'default';
            return view('bookingmodule::admin.booking.service-log', compact('booking', 'webPage', 'servicemen', 'customerAddress', 'category', 'subCategory', 'services', 'providers', 'zones', 'sort_by'));
        }

        Toastr::success(translate(ACCESS_DENIED['key']));
        return back();
    }

    public function reBookingOngoing()
    {
        return view('bookingmodule::admin.booking.rebooking-ongoing');
    }

    public function switchPaymentMethod($bookingId, Request $request)
    {
        $this->authorize('booking_can_manage_status');

        $validated = $request->validate([
            'payment_method' => 'required'
        ]);

        $booking = $this->booking->find($bookingId);
        $booking->payment_method = $request->payment_method;
        $booking->is_verified = 1;
        $booking->save();

        return response()->json(response_formatter(PAYMENT_METHOD_UPDATE_200), 200);
    }

    public function changeServiceLocation($bookingId, Request $request)
    {
        $this->authorize('booking_can_manage_status');

        $booking = $this->booking->find($bookingId);

        if (!$booking) {
            Toastr::error(translate('Booking not found'));
            return back();
        }

        $serviceAtProviderPlace = (int)((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0);

        if ($serviceAtProviderPlace == 0 && $request->service_location == 'provider') {
            Toastr::error(translate('Cannot switch to provider when provider service location is off'));
            return back();
        }

        if ($request->service_location == 'customer') {
            $existingAddress = json_decode($booking->service_address_location, true) ?? [];

            // Update only the changed values, keeping others untouched
            $updatedAddress = array_merge($existingAddress, [
                "lat" => $request->latitude ?? $existingAddress['lat'] ?? null,
                "lon" => $request->longitude ?? $existingAddress['lon'] ?? null,
                "city" => $request->city ?? $existingAddress['city'] ?? null,
                "street" => $request->street ?? $existingAddress['street'] ?? "",
                "zip_code" => $request->zip_code ?? $existingAddress['zip_code'] ?? "",
                "country" => $request->country ?? $existingAddress['country'] ?? null,
                "address" => $request->address ?? $existingAddress['address'] ?? null,
                "updated_at" => now()->toISOString(),
                "address_type" => $request->address_type ?? $existingAddress['address_type'] ?? "others",
                "contact_person_name" => $request->contact_person_name ?? $existingAddress['contact_person_name'] ?? null,
                "contact_person_number" => $request->contact_person_number ?? $existingAddress['contact_person_number'] ?? null,
                "address_label" => $request->address_label ?? $existingAddress['address_label'] ?? null,
                "zone_id" => $request->zone_id ?? $existingAddress['zone_id'] ?? null,
                "is_guest" => $request->is_guest ?? $existingAddress['is_guest'] ?? 0,
                "house" => $request->house ?? $existingAddress['house'] ?? "",
                "floor" => $request->floor ?? $existingAddress['floor'] ?? null]);

            $updateData = [
                'service_location' => 'customer',
                'service_address_location' => json_encode($updatedAddress), // Store updated JSON
            ];

        } else {
            $updateData = [
                'service_location' => 'provider'];
        }

        $booking->update($updateData);

        if ($request->has('next_all_booking_change')){
            $this->bookingRepeat
                ->where('booking_id', $booking->id)
                ->whereIn('booking_status', ['accepted', 'ongoing'])
                ->update($updateData);
        }else{
            if ($booking->repeat->isNotEmpty()) {
                $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                    $parts = explode('-', $repeat->readable_id);
                    $suffix = end($parts);
                    return $this->readableIdToNumber($suffix);
                });

                // Keep original collection for update
                $booking['repeats'] = $sortedRepeats->values()->toArray();

                // Work with the original model collection
                $sortedModelRepeats = $sortedRepeats->values();

                $nextService = $sortedModelRepeats->firstWhere('booking_status', 'ongoing')
                    ?? $sortedModelRepeats->firstWhere('booking_status', 'accepted')
                    ?? $sortedModelRepeats->firstWhere('booking_status', 'pending');

                if ($nextService) {
                    $nextService->update($updateData);
                }
            }
        }

        $user = $booking?->customer;
        $repeatOrRegular = $booking?->is_repeated ? 'repeat' : 'regular';
        if (isset($user) && $user?->fcm_token && $user?->is_active) {
            try {
                device_notification($user?->fcm_token, translate('service location updated'), null, null, $booking->id, 'booking', null, null, null, null, $repeatOrRegular);
            }catch (\Exception $exception) {
                //
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }

    public function repeatChangeServiceLocation($bookingId, Request $request)
    {
        $this->authorize('booking_can_manage_status');

        $booking = $this->bookingRepeat->find($bookingId);

        if (!$booking) {
            Toastr::error(translate('Booking not found'));
            return back();
        }

        $serviceAtProviderPlace = (int)((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0);

        if ($serviceAtProviderPlace == 0 && $request->service_location == 'provider') {
            Toastr::error(translate('Cannot switch to provider when provider service location is off'));
            return back();
        }

        if ($request->service_location == 'customer') {
            $existingAddress = json_decode($booking->service_address_location, true) ?? [];

            // Update only the changed values, keeping others untouched
            $updatedAddress = array_merge($existingAddress, [
                "lat" => $request->latitude ?? $existingAddress['lat'] ?? null,
                "lon" => $request->longitude ?? $existingAddress['lon'] ?? null,
                "city" => $request->city ?? $existingAddress['city'] ?? null,
                "street" => $request->street ?? $existingAddress['street'] ?? "",
                "zip_code" => $request->zip_code ?? $existingAddress['zip_code'] ?? "",
                "country" => $request->country ?? $existingAddress['country'] ?? null,
                "address" => $request->address ?? $existingAddress['address'] ?? null,
                "updated_at" => now()->toISOString(),
                "address_type" => $request->address_type ?? $existingAddress['address_type'] ?? "others",
                "contact_person_name" => $request->contact_person_name ?? $existingAddress['contact_person_name'] ?? null,
                "contact_person_number" => $request->contact_person_number ?? $existingAddress['contact_person_number'] ?? null,
                "address_label" => $request->address_label ?? $existingAddress['address_label'] ?? null,
                "zone_id" => $request->zone_id ?? $existingAddress['zone_id'] ?? null,
                "is_guest" => $request->is_guest ?? $existingAddress['is_guest'] ?? 0,
                "house" => $request->house ?? $existingAddress['house'] ?? "",
                "floor" => $request->floor ?? $existingAddress['floor'] ?? null]);

            $updateData = [
                'service_location' => 'customer',
                'service_address_location' => json_encode($updatedAddress), // Store updated JSON
            ];

        } else {
            $updateData = [
                'service_location' => 'provider'];
        }

        $booking->update($updateData);

        $mainBooking = $this->booking->find($booking->booking_id);
        if ($mainBooking) {
            $mainBooking->update($updateData);
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }
}
