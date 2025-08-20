<?php

namespace App\Events;

use App\Models\Reimbursement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReimbursementLevelAdvanced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $reimbursement, $divisionId, $newLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(Reimbursement $reimbursement, int $divisionId, string $newLevel)
    {
        $this->reimbursement = $reimbursement->load('employee');
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
        return 'reimbursement.level-advanced';
    }

    public function broadcastWith(): array
    {
        $d1 = \Carbon\Carbon::parse($this->reimbursement->date_start);
        $d2 = \Carbon\Carbon::parse($this->reimbursement->date_end);

        return [
            'reimbursement' => [
                'id' => $this->reimbursement->id,
                'status_1' => $this->reimbursement->status_1,
                'status_2' => $this->reimbursement->status_2,
                'date_start_fmt' => $d1->format('M d'),
                'date_end_fmt' => $d2->format('M d, Y'),
                'total_days' => $d1->diffInDays($d2) + 1,
                'created_at_fmt' => optional($this->reimbursement->created_at)->format('M d, Y'),
            ],
            'newLevel' => $this->newLevel,
        ];
    }
}
