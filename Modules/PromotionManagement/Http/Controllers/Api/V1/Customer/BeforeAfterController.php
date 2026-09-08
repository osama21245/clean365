<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\BeforeAfter;

class BeforeAfterController extends Controller
{
    public function __construct(private BeforeAfter $beforeAfter)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|numeric|min:1|max:200',
            'offset' => 'nullable|numeric|min:1|max:100000']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $limit = (int) ($request['limit'] ?? 20);
        $offset = (int) ($request['offset'] ?? 1);

        $items = $this->beforeAfter
            ->ofStatus(1)
            ->orderBy('sort_order')
            ->latest()
            ->paginate($limit, ['*'], 'offset', $offset)
            ->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $items), 200);
    }
}
