<?php

namespace Modules\FatooraZatcaForUltimatePos\Listeners;

use Modules\FatooraZatcaForUltimatePos\Services\ZatcaService;

// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Contracts\Queue\ShouldQueue;

class SellCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        ZatcaService::report($event->transaction);
    }
}
