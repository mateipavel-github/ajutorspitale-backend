<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\MetadataNeedType;
use Illuminate\Http\Request;
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
}