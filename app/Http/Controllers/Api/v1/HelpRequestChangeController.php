<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

use App\HelpRequestChangeNeed;
use Illuminate\Http\Request;
use App\HelpRequestChange;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HelpRequest as HelpRequestResource;
use App\HelpRequest;

class HelpRequestChangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $data = $request->post();

        //create new change
        $rc = new HelpRequestChange;
        $rc->help_request_id = $data['help_request_id'];
        $rc->user_id = Auth::check() ? Auth::user()->id : 1;
        $rc->change_type_id = $data['change_type_id'];
        $rc->user_comment = $data['user_comment'];
        $changes = $data;

        if(isset($data['needs']) && !empty($data['needs'])) {
            $changes['needs'] = true;
        }
        $rc->change_log = $changes;
        $rc->save();

        //add needs
        if(isset($data['needs']) && !empty($data['needs'])) {
            foreach($data['needs'] as $need) {
                $rc->needs()->create([
                    'need_type_id' => $need['need_type_id'],
                    'quantity' => $need['quantity']
                ])->save();
            }
        }

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'reloadHelpRequest' => new HelpRequestResource(HelpRequest::with(['changes','changes.needs','assigned_user'])->find($rc->help_request_id))
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
