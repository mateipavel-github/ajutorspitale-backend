<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Metadata;

use App\MetadataNeed;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;

class MetadataController extends Controller
{

    public function index(Request $request)
    {

        $needs = MetadataNeed::orderBy('label')->get();
        $counties = MetadataCounty::orderBy('label')->get();
        $medicalUnitTypes = MetadataMedicalUnitType::orderBy('label')->get();

        return [
            'needs' => Metadata::collection($needs),
            'counties' => Metadata::collection($counties),
            'medical_unit_types' => Metadata::collection($medicalUnitTypes)
        ];

    }

}
