<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\HelpRequest;
use App\PostingChange;
use App\PostingChangeNeed;
use App\MedicalUnit;
use Metadata;
use App\Exports\HelpRequestExport;

class StatsController extends Controller
{

    function init() {
        $this -> tables =  [
            'hr' => (new HelpRequest())->getTable(),
            'pc' => (new PostingChange())->getTable(),
            'pcn' => (new PostingChangeNeed())->getTable(),
            'mu' => (new MedicalUnit())->getTable()
        ];

        $this->approvedStatusId = Metadata::getRequestStatusIdFromSlug('approved');
    }

    public function requestsToCsv() {

        $this->init();

        $query = DB::table("posting_change_needs")
            ->join("posting_changes", 
                    "posting_change_needs.posting_change_id", "=", "posting_changes.id")
            ->join("help_requests", function($join) {
                $join->on("help_requests.id", "=", "posting_changes.item_id");
                $join->on("posting_changes.item_type", "=", DB::raw("'" . (new HelpRequest)->getPostingType('sql') . "'"));
            })
            ->join("metadata_counties", "help_requests.county_id", "=", "metadata_counties.id")
            ->join("metadata_medical_unit_types", "help_requests.medical_unit_type_id", "=", "metadata_medical_unit_types.id")
            ->join("metadata_need_types", "posting_change_needs.need_type_id", "=", "metadata_need_types.id")
            ->leftJoin("medical_units", "help_requests.medical_unit_id", "=", "medical_units.id")
            ->whereIn("help_requests.status", Metadata::getRequestStatusIdsFromSlugs(['approved','processed']))
            ->groupBy("posting_change_needs.need_type_id", "posting_changes.item_id")
            ->select(
                DB::raw('SUM(quantity) as a'),
                "metadata_need_types.label as b",
                "help_requests.medical_unit_name as c",
                "metadata_medical_unit_types.label as d",
                "medical_units.name as e",
                "help_requests.name as f",
                "help_requests.job_title as g",
                "help_requests.phone_number as h",
                "help_requests.created_at as i",
                "help_requests.updated_at as j",
                "medical_units.address as k",
                "medical_units.website as l",
                "medical_units.facebook_page as m",
                "metadata_counties.label as n",
                DB::raw("CONCAT('https://cereri.ajutorspitale.ro/admin/request/', help_requests.id) as o")
            )
            ->orderBy("metadata_need_types.label", "asc");
        
        
        //return response()->json($query->get());

        return (new HelpRequestExport($query))->download('requests.csv');

    }

    public function all() {

        $this->init();

        $statusSelectionIds = Metadata::getRequestStatusIdsFromSlugs(['approved','processed']);
        
        $aggregateNeedsQuery = DB::table($this->tables['hr'])
            ->leftJoin($this->tables['pc'], function($join) {
                $join->on($this->tables['hr'].'.id', '=', $this->tables['pc'].'.item_id');
                $join->on($this->tables['pc'].'.item_type', '=', DB::raw("'" . (new HelpRequest)->getPostingType('sql') . "'"));
            })->leftjoin(
                $this->tables['pcn'],
                $this->tables['pc'].'.id', '=', $this->tables['pcn'].'.posting_change_id'
            )->leftJoin(
                $this->tables['mu'],
                $this->tables['hr'].'.medical_unit_id', '=', $this->tables['mu'].'.id'
            )->select(
                $this->tables['hr'].'.*', 
                $this->tables['pcn'].'.need_type_id', 
                $this->tables['mu'].'.name as official_medical_unit_name', 
                DB::raw('SUM(quantity) as quantity')
            );
        
        if(count($statusSelectionIds)>0) {
            $aggregateNeedsQuery = $aggregateNeedsQuery->whereIn($this->tables['hr'].'.status', $statusSelectionIds);
        }

        $aggregateNeedsQuery = $aggregateNeedsQuery
            ->groupBy($this->tables['hr'].'.id', $this->tables['pcn'].'.need_type_id')
            ->orderBy($this->tables['hr'].'.id', 'desc');


        $aggregateNeeds = $aggregateNeedsQuery->get();

        // id, created_at, county_id, county_name, medical_unit_type (the slug from metadata_medical_unit_types)
        // medical_unit_id, medical_unit_name (added by them), official_medical_unit_name (if medical_unit_id is not null: the name from medical_units)
        // status (the slug from metadata_request_status_types), needs
    
        $currentIndex=-1;
        foreach($aggregateNeeds as $an) {

            if($currentIndex===-1 || $result[$currentIndex]['id'] !== $an->id ) {
                $currentIndex++;
            }

            if(!isset($result[$currentIndex])) {

                $result[$currentIndex] = [
                    'id' => $an->id,
                    'created_at' => $an->created_at,
                    'count_id' => $an->county_id,
                    'county_name' => $an->county_id ? Metadata::getCountyById($an->county_id)->label : null,
                    'medical_unit_type' => $an->medical_unit_type_id ? Metadata::getMedicalUnitTypeById($an->medical_unit_type_id)->slug : null,
                    'medical_unit_id' => $an->medical_unit_id,
                    'medical_unit_name' => $an->medical_unit_name,
                    'official_medical_unit_name' => $an->official_medical_unit_name,
                    'status' => Metadata::getRequestStatusById($an->status)->label,
                    'needs' => []
                ];

                if(Metadata::getRequestStatusById($an->status)->slug !== 'processed') {
                    $otherNeeds = explode("\n", $an->other_needs);
                    foreach($otherNeeds as $otherNeed) {
                        if(!empty($otherNeed)) {
                            $result[$currentIndex]['needs'][] = [
                                'name' => $otherNeed,
                                'amount' => 1,
                                'standard' => false
                            ];
                        }
                    }
                }

            }
            
            if($an->need_type_id) {
                $result[$currentIndex]['needs'][] = [
                    'name' => Metadata::getNeedTypeById($an->need_type_id)->label,
                    'amount' => $an->quantity,
                    'standard' => true
                ];
            }

        }

        return $result;

    }

    public function byCounty() {

        $this->init();

        $result = [];
        $statusSelectionIds = Metadata::getRequestStatusIdsFromSlugs(['approved','processed']);

        $aggregateNeedsQuery = DB::table($this->tables['hr'])
            ->whereNotNull($this->tables['hr'].'.county_id')
            ->leftJoin($this->tables['pc'], function($join) {
                $join->on($this->tables['hr'].'.id', '=', $this->tables['pc'].'.item_id');
                $join->on($this->tables['pc'].'.item_type', '=', DB::raw("'" . (new HelpRequest)->getPostingType('sql') . "'"));
            })->leftjoin(
                $this->tables['pcn'],
                $this->tables['pc'].'.id', '=', $this->tables['pcn'].'.posting_change_id'
            )->select(
                $this->tables['hr'].'.county_id', 
                $this->tables['pcn'].'.need_type_id',
                DB::raw('SUM(quantity) as quantity')
            );
        
        if(count($statusSelectionIds)>0) {
            $aggregateNeedsQuery = $aggregateNeedsQuery->whereIn($this->tables['hr'].'.status', $statusSelectionIds);
        }

        $aggregateNeedsQuery = $aggregateNeedsQuery
            ->groupBy($this->tables['hr'].'.county_id', $this->tables['pcn'].'.need_type_id');
            
        
        $aggregateNeeds = $aggregateNeedsQuery->get();

        foreach($aggregateNeeds as $an) {
            if($an->need_type_id) {  
                
                if(!isset($counties[$an->county_id])) {
                    $counties[$an->county_id] = [];
                }

                if(!isset($counties[$an->county_id]['needs']))
                $counties[$an->county_id]['needs'] = [];
            
                $counties[$an->county_id]['needs'][] = [
                    'name' => Metadata::getNeedTypeById($an->need_type_id)->label,
                    'amount' => $an->quantity,
                    'standard' => true
                ];
            }
        }

        $requestCountQuery = DB::table($this->tables['hr'])
                                ->select('county_id', DB::raw('COUNT(*) as requestCount'))
                                ->whereNotNull('county_id');
        if(count($statusSelectionIds)>0) {
            $requestCountQuery = $requestCountQuery->whereIn('status', $statusSelectionIds);
        }
        $requestCount = $requestCountQuery->groupBy('county_id')->get();

        foreach($requestCount as $rc) {
            $counties[$rc->county_id]['nr_requests'] = $rc->requestCount;
        }

        //other needs pentru "alte nevoi" neprocesate
        $requestsNotProcessed = DB::table($this->tables['hr'])
                                    ->where('status', $this->approvedStatusId)
                                    ->whereNotNull('county_id')
                                    ->get();
        foreach($requestsNotProcessed as $r) {
            $otherNeeds = explode("\n", $r->other_needs);
            foreach($otherNeeds as $otherNeed) {
                if(!empty($otherNeed)) {
                    $counties[$r->county_id]['needs'][] = [
                        'name' => $otherNeed,
                        'quanitity' => 1,
                        'standard' => false
                    ];
                }
            }
        }

        

        foreach($counties as $id => $data) {
            $result[] = [
                'county' => Metadata::getCountyById($id)->label,
                'needs' => isset($data['needs']) ? $data['needs'] : [],
                'nr_requests' => isset($data['nr_requests']) ? $data['nr_requests'] : 0
            ];
        }

        return $result;
    }

}
