<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\HelpRequest;
use App\Http\Resources\HelpRequestCollection;
use \App\Http\Resources\HelpRequest as HelpRequestResource;

class HelpRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $list = HelpRequest::with('assigned_user')->get();
        return new HelpRequestCollection($list);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->post();

        //create new change
        $hr = new HelpRequest;
        $hr->name = $data['name'];
        $hr->job_title = $data['job_title'];
        $hr->phone_number = $data['phone_number'];
        $hr->medical_unit_type_id = $data['medical_unit_type_id'];
        $hr->medical_unit_name = $data['medical_unit_name'];
        $hr->needs_text = $data['needs_text'];
        $hr->extra_info = $data['extra_info'];
        $hr->user_id = Auth::check() ? Auth::user()->id : 1;

        $hr->save();

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'newHelpRequest' => new HelpRequestResource(HelpRequest::with(['changes','changes.needs','assigned_user'])->find($hr->id))
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return new HelpRequestResource(HelpRequest::with(['changes','changes.needs','assigned_user'])->find($id));
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
