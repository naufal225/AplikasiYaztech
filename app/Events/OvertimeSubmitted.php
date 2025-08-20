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

class OvertimeSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $overtime, $divisionId;

    /**
     * Create a new event instance.
     */
    public function __construct(Overtime $overtime, int $divisionId)
    {
        $this->overtime = $overtime->load('employee');
        $this->divisionId = $divisionId; // supaya channel spesifik divisi
    }

    public function broadcastOn()
    {
        return new PrivateChannel("approver.division.{$this->divisionId}");
    }
    public function broadcastAs()
    {
        return 'overtime.submitted';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->overtime->id,
            'employee' => $this->overtime->employee->name,
            'status_1' => $this->overtime->status_1,
            'status_2' => $this->overtime->status_2,
            'created_at' => $this->overtime->created_at->toIso8601String(),
            'detail_url' => route('approver.overtimes.show', $this->overtime),
        ];
    }

}
