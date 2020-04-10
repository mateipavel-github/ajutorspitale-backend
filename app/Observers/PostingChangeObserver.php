<?php

namespace App\Observers;

use App\PostingChange;
use App\Posting;
use Illuminate\Support\Facades\Log;

class PostingChangeObserver
{

    private function _updateRequestNeedsAggregate(PostingChange $postingChange) {
        $posting = $postingChange->item_type::find($postingChange->item_id);
        $posting -> current_needs = true; //set it to anything, the mutator will fill in the correct value
        $posting -> save();
    }

    /**
     * Handle the help request change "created" event.
     *
     * @param  \App\PostingChange  $postingChange
     * @return void
     */
    public function created(PostingChange $postingChange)
    {
        $this ->_updateRequestNeedsAggregate($postingChange);

    }

    /**
     * Handle the help request change "updated" event.
     *
     * @param  \App\PostingChange  $postingChange
     * @return void
     */
    public function updated(PostingChange $postingChange)
    {
        $this ->_updateRequestNeedsAggregate($postingChange);
    }

    /**
     * Handle the help request change "deleted" event.
     *
     * @param  \App\PostingChange  $postingChange
     * @return void
     */
    public function deleted(PostingChange $postingChange)
    {
        $this ->_updateRequestNeedsAggregate($postingChange);
    }

    /**
     * Handle the help request change "restored" event.
     *
     * @param  \App\PostingChange  $postingChange
     * @return void
     */
    public function restored(PostingChange $postingChange)
    {
        $this ->_updateRequestNeedsAggregate($postingChange);
    }

    /**
     * Handle the help request change "force deleted" event.
     *
     * @param  \App\PostingChange  $postingChange
     * @return void
     */
    public function forceDeleted(PostingChange $postingChange)
    {
        $this ->_updateRequestNeedsAggregate($postingChange);
    }
}
