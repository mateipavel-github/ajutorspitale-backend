<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

use App\MetadataUserRoleType;
use Illuminate\Http\Request;
use App\Http\Resources\Metadata;

use App\MetadataNeedType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataChangeType;
use App\MetadataRequestStatusType;

class MetadataController extends Controller
{

    public function index(Request $request)
    {

        $needTypes = MetadataNeedType::orderBy('label')->get();
        $counties = MetadataCounty::orderBy('label')->get();
        $medicalUnitTypes = MetadataMedicalUnitType::orderBy('label')->get();
        $changeTypes = MetadataChangeType::orderBy('label')->get();
        $userRoleTypes = MetadataUserRoleType::orderBy('label')->get();
        $requestStatusTypes = MetadataRequestStatusType::orderBy('label')->get();

        return [
            'need_types' => Metadata::collection($needTypes),
            'counties' => Metadata::collection($counties),
            'medical_unit_types' => Metadata::collection($medicalUnitTypes),
            'change_types' => Metadata::collection($changeTypes),
            'user_role_types' => Metadata::collection($userRoleTypes),
            'request_status_types' => Metadata::collection($requestStatusTypes)
        ];

    }

    public function store(Request $request) {

        switch($request->post('metadata_type')) {
            case 'need_types': $t = new MetadataNeedType; break;
            case 'counties': $t = new MetadataCounty; break;
            case 'medical_unit_types': $t = new MetadataMedicalUnitType; break;
            case 'change_types': $t = new MetadataChangeType; break;
            case 'user_role_types': $t = new MetadataUserRoleType; break;
            case 'request_status_types': $t = new MetadataRequestStatusType; break;
            default: return ['success'=>false, 'error'=>'Metadata type not recognized']; break;
        }

        $t->label = $request->post('label');
        $t->status = 'active';
        if($t->save()) {
            return [
                'success'=>true,
                'data'=>[
                    'metadata_type'=>$request->post('metadata_type'),
                    'new_item'=> [
                        'id'=>$t->id,
                        'label'=>$t->label
                    ]
                ]
            ];
        }
        return ['success'=>false, 'error'=>'unknown'];

    }

}
