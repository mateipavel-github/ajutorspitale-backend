<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\HelpRequest;
use App\HelpRequestChange;
use App\HelpRequestChangeNeed;
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
            'hr' => (new HelpRequest()) -> getTable(),
            'hrc' => (new HelpRequestChange()) -> getTable(),
            'hrcn' => (new HelpRequestChangeNeed()) -> getTable(),
        ];
        $this->needTypes = MetadataNeedType::orderBy('label')->get()->toArray();
        $this->counties = MetadataCounty::orderBy('label')->get()->toArray();
        $this->medicalUnitTypes = MetadataMedicalUnitType::orderBy('label')->get()->toArray();
        $this->changeTypes = MetadataChangeType::orderBy('label')->get()->toArray();
        $this->userRoleTypes = MetadataUserRoleType::orderBy('label')->get()->toArray();
        $this->requestStatusTypes = MetadataRequestStatusType::orderBy('label')->get()->toArray();
    }

    public function associateBy($array, $field='id') {
        $result = [];
        foreach($array as $item) {
            $result[$item[$field]] = $item;
        }
        return $result;
    }

    public function byCounty() {

        $this->init();

        $result = [];
        $statuses = [];

        $counties = $this->associateBy($this->counties, 'id');
        $needs = $this->associateBy($this->needTypes, 'id');

        $statusSelectionIds = [];
        foreach($this->requestStatusTypes as $ps) {
            if(in_array($ps['slug'], $statuses)) {
                $statusSelectionIds[] = $ps['id'];
            }
        }

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
                'name' => $needs[$aggregateNeed->need_type_id]['label'],
                'amount' => $aggregateNeed->quantity
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

        foreach($counties as $id => $data) {
            $result[] = [
                'county' => isset($data['label']) ? $data['label'] : '',
                'needs' => isset($data['needs']) ? $data['needs'] : [],
                'nr_requests' => isset($data['nr_requests']) ? $data['nr_requests'] : 0
            ];
        }

        return $result;
    }

    public function all() {

    }


}
