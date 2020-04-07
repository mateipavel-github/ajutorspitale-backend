<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\HelpRequest;
use App\HelpRequestChange;
use App\HelpRequestChangeNeed;
use App\MedicalUnit;
use App\MetadataNeedType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataChangeType;
use App\MetadataUserRoleType;
use App\MetadataRequestStatusType;


class StatsController extends Controller
{

    function init() {
        $this -> tables =  [
            'hr' => (new HelpRequest())->getTable(),
            'hrc' => (new HelpRequestChange())->getTable(),
            'hrcn' => (new HelpRequestChangeNeed())->getTable(),
            'mu' => (new MedicalUnit())->getTable()
        ];
        $this->needTypes = MetadataNeedType::orderBy('label')->get()->toArray();
        $this->counties = MetadataCounty::orderBy('label')->get()->toArray();
        $this->medicalUnitTypes = MetadataMedicalUnitType::orderBy('label')->get()->toArray();
        $this->changeTypes = MetadataChangeType::orderBy('label')->get()->toArray();
        $this->userRoleTypes = MetadataUserRoleType::orderBy('label')->get()->toArray();
        $this->requestStatusTypes = MetadataRequestStatusType::orderBy('label')->get()->toArray();

        $this->approvedStatusId = 0;
        foreach($this->requestStatusTypes as $ps) {
            if($ps['slug']==='approved') {
                $this->approvedStatusId = $ps['id'];
            }
        }
    }

    public function associateBy($array, $field='id') {
        $result = [];
        foreach($array as $item) {
            $result[$item[$field]] = $item;
        }
        return $result;
    }

    public function getStatusIdsFromSlugs($slugs) {
        $statusSelectionIds = [];
        foreach($this->requestStatusTypes as $ps) {
            if(in_array($ps['slug'], $slugs)) {
                $statusSelectionIds[] = $ps['id'];
            }
        }
        return $statusSelectionIds;
    }

    public function all() {

        $this->init();

        $needTypes = $this->associateBy($this->needTypes, 'id');
        $counties = $this->associateBy($this->counties, 'id');
        $medicalUnitTypes = $this->associateBy($this->medicalUnitTypes, 'id');
        $statuses = $this->associateBy($this->requestStatusTypes, 'id');

        $statusSelectionSlugs = ['approved','processed'];
        $statusSelectionIds = $this->getStatusIdsFromSlugs($statusSelectionSlugs);
        
        $aggregateNeedsQuery = DB::table($this->tables['hrcn'])
            ->join(
                $this->tables['hrc'], 
                $this->tables['hrc'].'.id', '=', $this->tables['hrcn'].'.help_request_change_id'
            )->join(
                $this->tables['hr'],
                $this->tables['hr'].'.id', '=', $this->tables['hrc'].'.help_request_id'
            )->leftJoin(
                $this->tables['mu'],
                $this->tables['hr'].'.medical_unit_id', '=', $this->tables['mu'].'.id'
            )->select(
                $this->tables['hr'].'.*', 
                $this->tables['hrcn'].'.need_type_id', 
                $this->tables['mu'].'.name as official_medical_unit_name', 
                DB::raw('SUM(quantity) as quantity')
            );
        
        if(count($statusSelectionIds)>0) {
            $aggregateNeedsQuery = $aggregateNeedsQuery->whereIn($this->tables['hr'].'.status', $statusSelectionIds);
        }

        $aggregateNeedsQuery = $aggregateNeedsQuery
            ->groupBy($this->tables['hrcn'].'.need_type_id', $this->tables['hr'].'.id')
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
                    'county_name' => $an->county_id ? $counties[$an->county_id]['label'] : null,
                    'medical_unit_type' => $an->medical_unit_type_id ? $medicalUnitTypes[$an->medical_unit_type_id]['slug'] : null,
                    'medical_unit_id' => $an->medical_unit_id,
                    'medical_unit_name' => $an->medical_unit_name,
                    'official_medical_unit_name' => $an->official_medical_unit_name,
                    'status' => $statuses[$an->status]['label'],
                    'needs' => []
                ];

                $otherNeeds = explode("\n", $an->other_needs);
                foreach($otherNeeds as $otherNeed) {
                    if(!empty($otherNeed)) {
                        $result[$currentIndex]['needs'][] = [
                            'name' => $otherNeed,
                            'quanitity' => 1,
                            'standard' => false
                        ];
                    }
                }

            }
            
            $result[$currentIndex]['needs'][] = [
                'name' => $needTypes[$an->need_type_id]['label'],
                'amount' => $an->quantity,
                'standard' => true
            ];

        }

        return $result;

    }

    public function byCounty() {

        $this->init();

        $result = [];
        $statuses = ['approved','processed'];

        $counties = $this->associateBy($this->counties, 'id');
        $needTypes = $this->associateBy($this->needTypes, 'id');

        $statusSelectionIds = [];
        $statusSelectionIds = $this->getStatusIdsFromSlugs($statuses);

        $aggregateNeedsQuery = DB::table($this->tables['hrcn'])
            ->join(
                $this->tables['hrc'], 
                $this->tables['hrc'].'.id', '=', $this->tables['hrcn'].'.help_request_change_id')
            ->join(
                $this->tables['hr'],
                $this->tables['hr'].'.id', '=', $this->tables['hrc'].'.help_request_id'
            )
            ->select(
                $this->tables['hr'].'.county_id', 
                $this->tables['hrcn'].'.need_type_id', 
                DB::raw('SUM(quantity) as quantity')
            );
        
        if(count($statusSelectionIds)>0) {
            $aggregateNeedsQuery = $aggregateNeedsQuery->whereIn($this->tables['hr'].'.status', $statusSelectionIds);
        }

        $aggregateNeedsQuery = $aggregateNeedsQuery->groupBy($this->tables['hrcn'].'.need_type_id', $this->tables['hr'].'.county_id');
        
        $aggregateNeeds = $aggregateNeedsQuery->get();

        foreach($aggregateNeeds as $aggregateNeed) {
            if(!isset($counties[$aggregateNeed->county_id]['needs']))
                $counties[$aggregateNeed->county_id]['needs'] = [];
            
            $counties[$aggregateNeed->county_id]['needs'][] = [
                'name' => $needTypes[$aggregateNeed->need_type_id]['label'],
                'amount' => $aggregateNeed->quantity,
                'standard' => true
            ];
        }

        $requestCountQuery = DB::table($this->tables['hr'])
                                ->select('county_id', DB::raw('COUNT(*) as requestCount'));
        if(count($statusSelectionIds)>0) {
            $requestCountQuery = $requestCountQuery->whereIn('status', $statusSelectionIds);
        }
        $requestCount = $requestCountQuery->groupBy('county_id')->get();

        foreach($requestCount as $rc) {
            $counties[$rc->county_id]['nr_requests'] = $rc->requestCount;
        }

        //other needs pentru "alte nevoi" neprocesate
        $requestsNotProcessed = DB::table($this->tables['hr'])->where('status', $this->approvedStatusId)->get();
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
                'county' => isset($data['label']) ? $data['label'] : '',
                'needs' => isset($data['needs']) ? $data['needs'] : [],
                'nr_requests' => isset($data['nr_requests']) ? $data['nr_requests'] : 0
            ];
        }

        return $result;
    }

}
