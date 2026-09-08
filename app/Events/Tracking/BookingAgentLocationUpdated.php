<?php

namespace App\Events\Tracking;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingAgentLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  list<string>  $bookingIds
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $bookingIds,
        public readonly array $payload
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin.live-map')];

        foreach ($this->bookingIds as $bookingId) {
            $channels[] = new PrivateChannel('booking.'.$bookingId.'.tracking');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'BookingAgentLocationUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
