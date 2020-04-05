<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\MetadataNeedType;
use App\MetadataUserRoleType;
use App\User;
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
        $handle = fopen(resource_path('/csv/form_responses.csv'), 'r');
        $header = fgetcsv($handle);
        $volunteer_role = MetadataUserRoleType::where(['slug' => "volunteer"])->first();
        while (($data = fgetcsv($handle)) !== FALSE) {
            $this->saveUser($data, $volunteer_role);
            
        }
    }

    protected function saveUser($data, MetadataUserRoleType $volunteer_role)
    {
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
    }
}
