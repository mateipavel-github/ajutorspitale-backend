<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HelpRequestNote;

class HelpRequestNoteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->post();

        //create new request
        $note = new HelpRequestNote;

        $note->content = $data['content'];
        $note->help_request_id = $data['help_request_id'];
        $note->user_id = $request->user('api') ? $request->user('api')->id : null;

        $note->save();

        // return the new request so that the angular app can reload
        return [
            'success' => true,
            'data' => [
                'newNote' => $note->fresh()
            ]
        ];
    }
}