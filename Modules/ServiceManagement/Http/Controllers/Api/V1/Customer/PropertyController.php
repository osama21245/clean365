<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\Property;

class PropertyController extends Controller
{
    public function __construct(private Property $property)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'search' => 'nullable|string']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $properties = $this->property->ofStatus(1)
            ->when($request->filled('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                $query->where(function ($q) use ($keys) {
                    foreach ($keys as $key) {
                        $q->orWhere('name', 'LIKE', '%' . $key . '%')
                            ->orWhere('address', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $properties), 200);
    }

    public function show(string $id): JsonResponse
    {
        $property = $this->property->ofStatus(1)->where('id', $id)->first();

        if (!$property) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $property), 200);
    }
}
