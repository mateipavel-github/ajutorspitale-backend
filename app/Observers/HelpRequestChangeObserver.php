<?php

namespace App\Observers;

use App\HelpRequestChange;
use App\HelpRequest;
use Illuminate\Support\Facades\Log;

class HelpRequestChangeObserver
{

    private function _updateRequestNeedsAggregate(HelpRequestChange $helpRequestChange) {
        $hr = HelpRequest::find($helpRequestChange->help_request_id);
        $hr -> current_needs = true; //set it to anything, the mutator will fill in the correct value
        $hr -> save();
    }

    /**
     * Handle the help request change "created" event.
     *
     * @param  \App\HelpRequestChange  $helpRequestChange
     * @return void
     */
    public function created(HelpRequestChange $helpRequestChange)
    {
        $this ->_updateRequestNeedsAggregate($helpRequestChange);

    }

    /**
     * Handle the help request change "updated" event.
     *
     * @param  \App\HelpRequestChange  $helpRequestChange
     * @return void
     */
    public function updated(HelpRequestChange $helpRequestChange)
    {
        $this ->_updateRequestNeedsAggregate($helpRequestChange);
    }

    /**
     * Handle the help request change "deleted" event.
     *
     * @param  \App\HelpRequestChange  $helpRequestChange
     * @return void
     */
    public function deleted(HelpRequestChange $helpRequestChange)
    {
        $this ->_updateRequestNeedsAggregate($helpRequestChange);
    }

    /**
     * Handle the help request change "restored" event.
     *
     * @param  \App\HelpRequestChange  $helpRequestChange
     * @return void
     */
    public function restored(HelpRequestChange $helpRequestChange)
    {
        $this ->_updateRequestNeedsAggregate($helpRequestChange);
    }

    /**
     * Handle the help request change "force deleted" event.
     *
     * @param  \App\HelpRequestChange  $helpRequestChange
     * @return void
     */
    public function forceDeleted(HelpRequestChange $helpRequestChange)
    {
        $this ->_updateRequestNeedsAggregate($helpRequestChange);
    }
}
