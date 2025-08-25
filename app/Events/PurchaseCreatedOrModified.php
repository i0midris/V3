<?php

namespace App\Events;

use App\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseCreatedOrModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    public $isDeleted;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction, $isDeleted = false)
    {
        $this->transaction = $transaction;
        $this->isDeleted = $isDeleted;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
