<?php

namespace App\Events;

use App\Models\Investment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvestmentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $investment;
    public $updateType;
    public $updateData;

    /**
     * Create a new event instance.
     */
    public function __construct(Investment $investment, string $updateType, array $updateData = [])
    {
        $this->investment = $investment;
        $this->updateType = $updateType;
        $this->updateData = $updateData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('investment-updates'),
        ];
    }
}
