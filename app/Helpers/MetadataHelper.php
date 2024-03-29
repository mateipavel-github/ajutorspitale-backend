<?php

namespace App\Helpers;
use App\MetadataChangeType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataRequestStatusType;
use App\MetadataUserRoleType;
use App\MetadataNeedType;
use App\MetadataOfferStatusType;
use App\MetadataDeliveryStatusType;
use App\MetadataDeliveryPlanStatusType;

class MetadataHelper {

    public function __construct() {
        $this -> metadata = [];
    }

    public function __get($metadataType) {
        if(!isset($this->metadata[$metadataType])) {
            return $this->metadata[$metadataType] = $this->_load($metadataType);
        } else {
            return $this->metadata[$metadataType];
        }
    }


    public function getChangeTypeIdFromSlug($slug) {
        return $this->change_types->firstWhere('slug', $slug)['id'];
    }
    public function getRequestStatusIdsFromSlugs($slugs) {
        return $this->request_status_types->whereIn('slug', $slugs)->pluck('id');
    }
    public function getRequestStatusIdFromSlug($slug) {
        return $this->request_status_types->firstWhere('slug', $slug)['id'];
    }

    public function getOfferStatusIdsFromSlugs($slugs) {
        return $this->offer_status_types->whereIn('slug', $slugs)->pluck('id');
    }
    public function getOfferStatusIdFromSlug($slug) {
        return $this->offer_status_types->firstWhere('slug', $slug)['id'];
    }

    public function getDeliveryStatusIdsFromSlugs($slugs) {
        return $this->delivery_status_types->whereIn('slug', $slugs)->pluck('id');
    }
    public function getDeliveryStatusIdFromSlug($slug) {
        return $this->delivery_status_types->firstWhere('slug', $slug)['id'];
    }


    public function getSorted($metadataType, $sortKey) {
        return $this->$metadataType->sortBy($sortKey)->values();
    }

    public function getNeedTypeById($id) {
        return $this->_getById('need_types', $id);
    }
    public function getRequestStatusById($id) {
        return $this->_getById('request_status_types', $id);
    }
    public function getMedicalUnitTypeById($id) {
        return $this->_getById('medical_unit_types', $id);
    }
    public function getCountyById($id) {
        return $this->_getById('counties', $id);
    }
    public function getDeliveryStatusById($id) {
        return $this->_getById('delivery_status_types', $id);
    }
    

    public function mergeIntoAndDelete($type, $idToDelete, $idToMergeInto) {
        switch($type) {
            case 'medical_unit_types':
                //medical_units, offers, requests
                \App\MedicalUnit::where('medical_unit_type_id', $idToDelete)->update(['medical_unit_type_id' => $idToMergeInto]);
                \App\HelpRequest::where('medical_unit_type_id', $idToDelete)->update(['medical_unit_type_id' => $idToMergeInto]);
                MetadataMedicalUnitType::find($idToDelete)->delete();
                return ['success'=>true];
                break;
            default:
                return ['success'=>false, 'error'=>'Delete "'.$type.'" not implemented'];
                break;
        }
    }

    private function _getById($metadataType, $id) {
        // todo implement caching 
        $return = $this->$metadataType->firstWhere('id', $id);
        if($return === null) {
            $return = new \stdClass(); 
            $return -> label = $metadataType.' not found';
        }
        return $return;
    }

    private function _load($metadataType) {
        switch($metadataType) {
            case 'user_role_types': 
                return MetadataUserRoleType::all();
                break;
            case 'need_types': 
                return MetadataNeedType::all();
                break;
            case 'request_status_types': 
                return MetadataRequestStatusType::all();
                break;
            case 'offer_status_types': 
                return MetadataOfferStatusType::all();
            break;
            case 'delivery_status_types': 
                return MetadataDeliveryStatusType::all();
            break;
            case 'delivery_plan_status_types':
                return MetadataDeliveryPlanStatusType::all();
                break;
            case 'counties': 
                return MetadataCounty::all();
                break;
            case 'change_types': 
                return MetadataChangeType::all();
                break;
            case 'medical_unit_types': 
                return MetadataMedicalUnitType::all();
                break;
            default:
                echo $metadataType.' - not found';
            break;
        }
    }

    
}