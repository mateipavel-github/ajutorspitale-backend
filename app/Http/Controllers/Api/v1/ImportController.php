<?php

namespace App\Http\Controllers\Api\v1;

use App\HelpRequest;
use App\HelpRequestChange;
use App\HelpRequestChangeNeed;
use App\HelpRequestNote;
use App\Http\Controllers\Controller;

use App\MedicalUnit;
use App\MetadataChangeType;
use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataNeedType;
use App\MetadataRequestStatusType;
use App\MetadataUserRoleType;
use App\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportController extends Controller
{

    public function need_types()
    {
        $data = array_map('str_getcsv', file(resource_path('/csv/date_publice.csv')));
        $table_header = array_shift($data);
        $need_types = array_splice($table_header, 3, 16);
        $existing_need_types = [];
        $new_need_types = [];
        foreach ($need_types as $need_type) {
            $need_type_db = MetadataNeedType::where(['slug' => Str::slug($need_type)])->first();
            if (empty($need_type_db)) {
                $need_type_db = new MetadataNeedType();
                $need_type_db->fill([
                    'label' => $need_type,
                    'slug' => Str::slug($need_type),
                ]);
                $need_type_db->save();
                $new_need_types[] = $need_type_db->label;
                continue;
            }
            $existing_need_types[] = $need_type_db->label;
        }
        dd("Existing needs", $existing_need_types, "New needs", $new_need_types, "Done");
    }

    public function form_responses()
    {
        set_time_limit(6000);
        $handle = fopen(resource_path('/csv/form_responses.csv'), 'r');
        $header = fgetcsv($handle);
        $volunteer_role = MetadataUserRoleType::where(['slug' => "volunteer"])->first();

        while (($data = fgetcsv($handle)) !== FALSE) {
            $request_volunteer = $this->saveUser($data, $volunteer_role);
            $existing_help_request = HelpRequest::where(['created_at' => Carbon::createFromFormat('m/d/Y H:i:s', $data[0])])->first();
            try {
                $this->saveHelpRequest($data, $request_volunteer, $existing_help_request);
            } catch (\Exception $exception) {
                dd($header, $data, $exception);
            }
        }

        //populating the other_needs
        $this->populateRequestNeeds();
    }

    protected function saveUser($data, MetadataUserRoleType $volunteer_role)
    {
        $existing_user = null;
        $users_on_multiple_lines = explode(PHP_EOL, $data[9]);
        foreach ($users_on_multiple_lines as $user_name) {
            $user_name = ucwords(strtolower(trim($user_name)));
            $existing_user = User::where(['name' => $user_name])->first();
            if (empty($existing_user)) {
                $existing_user = new User();
                $existing_user->fill([
                    "name" => $user_name,
                    "role_type_id" => $volunteer_role->id,
                    "email" => Str::random(10) . "@gmail.com",
                    "phone_number" => str_pad(mt_rand(1, 99999999), 10, '0', STR_PAD_LEFT),
                    "password" => Hash::make(Str::random())
                ]);
                $existing_user->save();
            }

        }
        return $existing_user;
    }

    protected function saveHelpRequest($data, User $request_volunteer, HelpRequest $existing_help_request = null)
    {
        $status = null;
        $medical_unit_type = MetadataMedicalUnitType::where(['label' => $data[7]])->first();

        $county = MetadataCounty::where(['label' => $data[5]])->first();
        switch (strtolower(trim($data[11]))) {
            case "da":
                $status = MetadataRequestStatusType::where(['slug' => "approved"])->first();
                break;
            case "nu":
                $status = MetadataRequestStatusType::where(['slug' => "rejected"])->first();
                break;
            case "rezolvat":
                $status = MetadataRequestStatusType::where(['slug' => "complete"])->first();
                break;
            default:
                $status = MetadataRequestStatusType::where(['slug' => "new"])->first();
                break;
        }
        if ($existing_help_request) {
            $help_request = $existing_help_request;
        } else {
            $help_request = new HelpRequest();
        }

        $help_request->user_id = null;
        $help_request->assigned_user_id = $request_volunteer->id;
        $help_request->name = ucwords(strtolower(trim($data[1])));
        $help_request->phone_number = trim($data[2]);
        $help_request->medical_unit_type_id = !empty($medical_unit_type) ? $medical_unit_type->id : null;
        $help_request->medical_unit_name = trim($data[3]);
        $help_request->job_title = trim($data[8]);
        $help_request->extra_info = preg_replace('/[[:^print:]]/', '', trim($data[6]));
        $help_request->needs_text = $data[4];
        $help_request->status = $status->id;
        $help_request->county_id = !empty($county) ? $county->id : null;
        $help_request->caller_observations = $data[10];
        $help_request->created_at = Carbon::createFromFormat('m/d/Y H:i:s', $data[0]);
        $help_request->save();
        return $help_request;
    }

    protected function getNeedType($need_type)
    {
        $need_type_db = MetadataNeedType::where(['slug' => Str::slug($need_type)])->first();
        if (empty($need_type_db)) {
            $need_type_db = new MetadataNeedType();
            $need_type_db->fill([
                'label' => $need_type,
                'slug' => Str::slug($need_type),
            ]);
            $need_type_db->save();
        }
        return $need_type_db;
    }

    protected function populateRequestNeeds()
    {
        HelpRequestChange::truncate();
        HelpRequestChangeNeed::truncate();
        HelpRequestNote::truncate();
        $handle = fopen(resource_path('/csv/date_publice.csv'), 'r');
        $header = fgetcsv($handle);
        $need_types = array_splice($header, 3, 16);
        $parsed_need_types = [];

        foreach ($need_types as $key => $need_type) {
            $parsed_need_types[$key + 3] = $this->getNeedType($need_type);
        }

        while (($data = fgetcsv($handle)) !== FALSE) {
            $existing_help_request = HelpRequest::where(['created_at' => Carbon::createFromFormat('m/d/Y H:i:s', $data[0])])->first();
            $existing_help_request->other_needs = $data[20];
            $existing_help_request->save();


            switch (strtolower(trim($data[2]))) {
                case "da":
                    $status = MetadataRequestStatusType::where(['slug' => "approved"])->first();
                    break;
                case "nu":
                    $status = MetadataRequestStatusType::where(['slug' => "rejected"])->first();
                    break;
                case "rezolvat":
                    $status = MetadataRequestStatusType::where(['slug' => "complete"])->first();
                    break;
                default:
                    $status = MetadataRequestStatusType::where(['slug' => "new"])->first();
                    break;
            }

            $change_type = MetadataChangeType::where(['slug' => 'new_request'])->first();

            $help_request_change = new HelpRequestChange();
            $help_request_change->help_request_id = $existing_help_request->id;
            $help_request_change->user_id = $existing_help_request->assigned_user_id;
            $help_request_change->status = $status->id;
            $help_request_change->change_type_id = $change_type->id;
            $help_request_change->change_log = json_encode(['needs' => true]);
            $help_request_change->save();

            $helpRequestNote = new HelpRequestNote();
            $helpRequestNote->user_id = $existing_help_request->assigned_user_id;
            $helpRequestNote->help_request_id = $existing_help_request->id;
            $helpRequestNote->content = $existing_help_request->caller_observations;
            $helpRequestNote->save();

            foreach ($data as $column_key => $column) {
                if ($column_key >= 3 && $column_key <= 18) {
                    if ($column !== '') {
                        $help_request_change_need = new HelpRequestChangeNeed();
                        $help_request_change_need->need_type_id = $parsed_need_types[$column_key]->id;
                        $help_request_change_need->quantity = $column;
                        $help_request_change_need->help_request_change_id = $help_request_change->id;
                        $help_request_change_need->save();
                    }
                }
            }
        }
    }

    public function medicalUnits()
    {
        $counties = MetadataCounty::all();
        foreach ($counties as $county) {
            $countyLabelsToIds[strtolower(str_replace(explode(',', 'â,ă,î,ț,ș,Ă,Î,Ș,Ț'), explode(',', 'a,a,i,t,s,A,I,S,T'), $county->label))] = $county->id;
        }


        $handle = fopen(resource_path('/csv/spitale_de_stat.csv'), 'r');
        $header = fgetcsv($handle);
        $table_header = array_splice($header, 3, 16);
        $units_not_inserted = [];
        $medical_unit_types['judetean'] = DB::table('metadata_medical_unit_types')->where(['label' => 'spital județean de stat'])->first();
        $medical_unit_types['orasanesc'] = DB::table('metadata_medical_unit_types')->where(['label' => 'spital orășenesc de stat'])->first();

        while (($data = fgetcsv($handle)) !== FALSE) {
            $existing_medical_unit = MedicalUnit::where(['government_id' => $data[0]])->first();
            if (empty($existing_medical_unit)) {
                $units_not_inserted[] = $data;
                continue;
            }
            $existing_medical_unit->name_without_diacritics = $data[2];
            $existing_medical_unit->name_without_council = $data[5];
            $existing_medical_unit->latitude = $data[14];
            $existing_medical_unit->longitude = $data[15];
            $existing_medical_unit->facebook_page = $data[9];
            $existing_medical_unit->website = $data[7];
            $existing_medical_unit->address = $data[13];
            if (empty($existing_medical_unit->county_id) && $data[1] !== "RETEA SANITARA PROPR" && !empty($data[1])) {
                //county names are not normalized... missing hyphen on some; this is an exception
                if ($data[1] === "Caras Severin") {
                    $data[1] = "caras-severin";
                }
                if ($data[1] === "Satu Mare") {
                    $data[1] = "satu-mare";
                }
                $existing_medical_unit->county_id = isset($countyLabelsToIds[strtolower($data[1])]) ? (int)$countyLabelsToIds[strtolower($data[1])] : null;
                $existing_medical_unit->county = $data[1];
            }
            if (empty($existing_medical_unit->medical_unit_type_id) && !empty($data['6'])) {
                $csv_medical_unit_type_name = strtolower(str_replace(explode(',', 'â,ă,î,ț,ș,Ă,Î,Ș,Ț'), explode(',', 'a,a,i,t,s,A,I,S,T'), $data['6']));
                if (empty($medical_unit_types[$csv_medical_unit_type_name])) {
                    $db_medical_unit_types = DB::table('metadata_medical_unit_types')->where('label', "LIKE", "%" . $csv_medical_unit_type_name . "%")->get();
                    if ($db_medical_unit_types->isEmpty() || $db_medical_unit_types->count() > 1) {
                        dd(__("Something is wrong with the medical unit types"), $db_medical_unit_types, __("Searched for"), $csv_medical_unit_type_name, $data, $data['6']);
                    }
                    $medical_unit_types[$csv_medical_unit_type_name] = $db_medical_unit_types->first();
                }

                $existing_medical_unit->medical_unit_type_id = $medical_unit_types[$csv_medical_unit_type_name]->id;
            }
            $existing_medical_unit->save();

            if (empty($existing_medical_unit->county_id) && $data[1] !== "RETEA SANITARA PROPR" && !empty($data[1])) {
                dd("could not find the county", $data[1], $existing_medical_unit, $data, $countyLabelsToIds);
            }

            if (empty($existing_medical_unit->medical_unit_type_id) && !empty($data['6'])) {
                dd(__("Something is wrong with the medical unit types"), $csv_medical_unit_type_name);
            }
        }
        dd("finished", $units_not_inserted);
    }
}
