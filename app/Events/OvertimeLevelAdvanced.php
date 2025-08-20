<?php

namespace App\Events;

use App\Models\Overtime;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OvertimeLevelAdvanced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $overtime, $divisionId, $newLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(Overtime $overtime, int $divisionId, string $newLevel)
    {
        $this->overtime = $overtime->load('employee');
        $this->divisionId = $divisionId;
        $this->newLevel = $newLevel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): PrivateChannel
    {
        return $this->newLevel === 'manager'
            ? new PrivateChannel("manager.division.{$this->divisionId}")
            : new PrivateChannel("approver.division.{$this->divisionId}");
    }

    public function broadcastAs()
    {
        return 'overtime.level-advanced';
    }

    public function broadcastWith(): array
    {
        $d1 = \Carbon\Carbon::parse($this->overtime->date_start);
        $d2 = \Carbon\Carbon::parse($this->overtime->date_end);

        return [
            'overtime' => [
                'id' => $this->overtime->id,
                'status_1' => $this->overtime->status_1,
                'status_2' => $this->overtime->status_2,
                'date_start_fmt' => $d1->format('M d'),
                'date_end_fmt' => $d2->format('M d, Y'),
                'total_days' => $d1->diffInDays($d2) + 1,
                'created_at_fmt' => optional($this->overtime->created_at)->format('M d, Y'),
            ],
            'newLevel' => $this->newLevel,
        ];
    }
}
