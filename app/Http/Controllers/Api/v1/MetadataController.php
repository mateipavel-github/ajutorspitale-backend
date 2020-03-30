<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Resources\Metadata;

use App\MetadataNeedType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataChangeType;

class MetadataController extends Controller
{

    public function index(Request $request)
    {

        $needTypes = MetadataNeedType::orderBy('label')->get();
        $counties = MetadataCounty::orderBy('label')->get();
        $medicalUnitTypes = MetadataMedicalUnitType::orderBy('label')->get();
        $changeTypes = MetadataChangeType::orderBy('label')->get();

        return [
            'need_types' => Metadata::collection($needTypes),
            'counties' => Metadata::collection($counties),
            'medical_unit_types' => Metadata::collection($medicalUnitTypes),
            'change_types' => Metadata::collection($changeTypes)
        ];

    }

}
