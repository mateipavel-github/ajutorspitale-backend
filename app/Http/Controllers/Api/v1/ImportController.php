<?php

namespace App\Http\Controllers\Api\v1;

use App\HelpRequest;
use App\Http\Controllers\Controller;

use App\MetadataCounty;
use App\MetadataMedicalUnitType;
use App\MetadataNeedType;
use App\MetadataRequestStatusType;
use App\MetadataUserRoleType;
use App\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
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
        $handle = fopen(resource_path('/csv/date_publice.csv'), 'r');
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            $existing_help_request = HelpRequest::where(['created_at' => Carbon::createFromFormat('m/d/Y H:i:s', $data[0])])->first();
            $existing_help_request->other_needs = $data[21];
            $existing_help_request->save();
        }
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
}
