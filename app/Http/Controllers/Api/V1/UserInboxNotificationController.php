<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserInboxNotification;
use App\Support\NotificationLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class UserInboxNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|numeric|min:1|max:200',
            'offset' => 'nullable|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(response_formatter(DEFAULT_401), 401);
        }

        $limit = (int) ($request->input('limit') ?: pagination_limit());
        $offset = (int) ($request->input('offset') ?: 1);

        $page = UserInboxNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($limit, ['*'], 'offset', $offset)
            ->withPath('');

        $locale = (string) ($user->current_language_key ?? config('app.locale') ?: 'en');

        $page->getCollection()->transform(function (UserInboxNotification $row) use ($locale) {
            $titleMap = is_array($row->title) ? $row->title : NotificationLocale::decodeMap($row->title);
            $descMap = is_array($row->description)
                ? $row->description
                : NotificationLocale::decodeMap($row->description);

            return [
                'id' => $row->id,
                'type' => $row->type,
                'title' => NotificationLocale::resolve($titleMap ?? [], $locale),
                'description' => NotificationLocale::resolve($descMap ?? [], $locale),
                'url' => $row->url,
                'view' => $row->view,
                'data' => $row->data,
                'created_at' => $row->created_at,
            ];
        });

        return response()->json(response_formatter(DEFAULT_200, $page), 200);
    }

    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(response_formatter(DEFAULT_401), 401);
        }

        $row = UserInboxNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->first();

        if (! $row) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $locale = (string) ($user->current_language_key ?? config('app.locale') ?: 'en');
        $titleMap = is_array($row->title) ? $row->title : NotificationLocale::decodeMap($row->title);
        $descMap = is_array($row->description)
            ? $row->description
            : NotificationLocale::decodeMap($row->description);

        return response()->json(response_formatter(DEFAULT_200, [
            'id' => $row->id,
            'type' => $row->type,
            'title' => NotificationLocale::resolve($titleMap ?? [], $locale),
            'description' => NotificationLocale::resolve($descMap ?? [], $locale),
            'url' => $row->url,
            'view' => $row->view,
            'data' => $row->data,
            'created_at' => $row->created_at,
        ]), 200);
    }
}
