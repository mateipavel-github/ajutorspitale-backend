<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;

use App\MetadataUserRoleType;
use Illuminate\Http\Request;
use App\Http\Resources\Metadata as MetadataResource;

use App\MetadataNeedType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataChangeType;
use App\MetadataRequestStatusType;
use App\MedicalUnit;

use Metadata;

class MetadataController extends Controller
{

    public function medicalUnits(Request $request) {

        $list = MedicalUnit::select("*");
        if ($request->get('county_id')) {
            $list->where(['county_id' => $request->get("county_id")]);
        }

        if ($request->get('filter')) {
            $list->where('name', 'LIKE', '%'.$request->get('filter').'%');
        }

        $results = $list->get()->toArray();
        return response()->json([
            "data" => [
                'items' => $results
            ],
            "message" => __("Got collection"),
            "success" => true
        ]);
    }


    public function index(Request $request)
    {

        $return = [];
        $list = ['need_types', 'counties','medical_unit_types','change_types','user_role_types','request_status_types'];
        foreach($list as $metadataType) {
            $return[$metadataType] = Metadata::getSorted($metadataType, 'label')->all();
        }

        return $return;
    }

    public function store(Request $request) {

        $type = 'App\\';

        switch($request->post('metadata_type')) {
            case 'need_types': $type .= 'MetadataNeedType'; break;
            case 'counties': $type .= 'MetadataCounty'; break;
            case 'medical_unit_types': $type .= 'MetadataMedicalUnitType'; break;
            case 'change_types': $type .= 'MetadataChangeType'; break;
            case 'user_role_types': $type .= 'MetadataUserRoleType'; break;
            case 'request_status_types': $type .= 'MetadataRequestStatusType'; break;
            default: return ['success'=>false, 'error'=>'Metadata type not recognized']; break;
        }

        if($request -> post('id')) {
            $t = call_user_func($type.'::find', $request -> post('id'));
            if($new_id = $request->post('new_id')) {
                $t -> id = $new_id;
            }
        } else {
            $t = new $type;
        }

        $t->label = $request->post('label');

        $typesWithSlug = ['user_role_types', 'change_types', 'medical_unit_types', 'request_status_types', 'counties', 'need_types'];

        if(in_array($request->post('metadata_type'), $typesWithSlug)) {
            $t->slug = $request->post('slug');
        }

        if($t->save()) {
            return [
                'success'=>true,
                'data'=>[
                    'metadata_type'=>$request->post('metadata_type'),
                    'new_item'=> [
                        'id'=>$t->id,
                        'label'=>$t->label,
                        'slug'=>$t->slug
                    ]
                ]
            ];
        }
        return ['success'=>false, 'error'=>'unknown'];

    }

}
