<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdBroadcast;
use App\Models\AdBroadcastAck;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdBroadcastAckController extends Controller
{
    /**
     * Record that the authenticated user received or opened a dashboard push ad.
     * Idempotent per user and event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'send_id' => ['required', 'uuid', 'exists:ad_broadcasts,send_id'],
            'event' => ['sometimes', 'string', Rule::in(['opened', 'delivered'])],
        ]);

        $event = $validated['event'] ?? 'opened';

        $broadcast = AdBroadcast::query()->where('send_id', $validated['send_id'])->firstOrFail();

        $ack = AdBroadcastAck::query()->firstOrCreate(
            [
                'ad_broadcast_id' => $broadcast->id,
                'user_id' => $request->user()->id,
                'event' => $event,
            ],
            [],
        );

        if ($ack->wasRecentlyCreated && $event === 'opened') {
            AdBroadcast::query()->whereKey($broadcast->id)->increment('opened_count');
        }

        return response()->json(['ok' => true]);
    }
}
