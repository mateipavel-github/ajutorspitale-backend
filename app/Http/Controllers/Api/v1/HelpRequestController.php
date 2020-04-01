<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\HelpRequest;
use App\Http\Resources\HelpRequestCollection;
use \App\Http\Resources\HelpRequest as HelpRequestResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Log;

class HelpRequestController extends Controller
{

    public function massAssignToCurrentUser(Request $request) {
        $howMany = $request->post('howMany');
        $userId = $request->user('api')->id;

        $requestIds  = HelpRequest::whereIn('status', [1,2])->whereNull('assigned_user_id')->pluck('id')->take($howMany);
        HelpRequest::whereIn('id', $requestIds)->update(array('assigned_user_id' => $userId));

        return ['success'=>true];
    }

    /**
     * Display a listing of the resource.
     *
     * @return HelpRequestCollection
     */
    public function index()
    {

        $list = HelpRequest::with('assigned_user')->get();
        return new HelpRequestCollection($list);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $data = $request->post();

        //create new request
        $hr = new HelpRequest;

        $hr->name = $data['name'];
        $hr->job_title = $data['job_title'];
        $hr->phone_number = $data['phone_number'];
        $hr->medical_unit_type_id = $data['medical_unit_type_id'];
        $hr->medical_unit_name = $data['medical_unit_name'];
        $hr->needs_text = $data['needs_text'];
        $hr->extra_info = $data['extra_info'];
        $hr->user_id = $request->user('api') ? $request->user('api')->id : null;

        $hr->saveWithChanges(['change_type_id'=>1, 'changes'=>$hr->toArray()]);

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
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $action = $request->get('action');

        $hr = HelpRequest::find($id);
        switch($action) {
            case 'assignCurrentUser':
                $hr -> assigned_user_id = $request->user('api')->id;
                $return = [ 'assigned_user' => new UserResource(Auth::user()) ];
                break;
            case 'unassignCurrentUser':
                $hr -> assigned_user_id = null;
                $return = [ 'assigned_user' => null ];
                break;
        }

        $hr->save();
        $return['success'] = true;
        return $return;
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
