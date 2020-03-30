<?php

use Illuminate\Database\Seeder;

class FirstSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create metadata
        $this -> medicalUnitTypes();
        $this -> counties();
        $this -> needTypes();
        $this -> changeTypes();

        //create admin user
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'matei+admin@konk-media.com',
            'password' => Hash::make('password'),
        ]);

        //create one doctor/requester account
        $doctorId = DB::table('users')->insertGetId([
            'name' => 'doctor',
            'email' => 'matei+doctor@konk-media.com',
            'password' => Hash::make('password'),
        ]);

        //create one volunteer account
        $volunteerId = DB::table('users')->insertGetId([
            'name' => 'volunteer',
            'email' => 'matei+volunteer@konk-media.com',
            'password' => Hash::make('password'),
        ]);

        //create requests
        $requestId = DB::table('help_requests')->insertGetId($originalRequestData = [
            'user_id' => $doctorId,
            'name' => 'Domnul Doctor',
            'phone_number' => '+40722278567',
            'job_title' => 'Șef de secție ATI',
            'medical_unit_name' => 'Spitalul de Urgențe Floreasca',
            'medical_unit_type_id' => 1,
            'assigned_user_id' => $volunteerId
        ]);

        $originalRequestData['needs'] = true;

        //create request change
        $requestChangeId = DB::table('help_request_changes')->insertGetId([
            'help_request_id' => $requestId,
            'changes' => json_encode($originalRequestData),
            'user_id' => $volunteerId,
            'user_comment' => 'first entry',
            'status' => 'final',
            'change_type_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        //create request change needs
        $need_types = DB::table('metadata_need_types')->get();
        foreach($need_types as $need_type) {
            DB::table('help_request_change_needs')->insert([
                'help_request_change_id' => $requestChangeId,
                'need_type_id' => $need_type -> id,
                'quantity' => Arr::random([12000,15000,10,300,200])
            ]);
        }

    }

    public function medicalUnitTypes() {

        $list = ['spital județean de stat', 'spital orășenesc de stat', 'alt fel de spital de stat (maternitate; cu o anumită specializare; etc)', 'spital privat', 'direcția ambulanțe', 'medic de familie'];

        foreach($list as $item) {
            DB::table('metadata_medical_unit_types')->insert([
                'label' => $item,
                'status' => 'active'
            ]);
        }

    }

    public function changeTypes() {

        $list = ['Delivery','Typo','Update by solicitor','Update by volunteer at the request of solicitor'];

        foreach($list as $item) {
            DB::table('metadata_change_types')->insert([
                'label' => $item,
                'status' => 'active'
            ]);
        }

    }

    public function counties() {

        $list = ['Alba','Argeș','Arad','București','Bacău','Bihor','Bistrița Năsăud','Brăila','Botoșani','Brașov','Buzău','Cluj','Călărași','Caraș-Severin','Constanța','Covasna','Dâmbovița','Dolj','Gorj','Galați','Giurgiu','Hunedoara','Harghita','Ilfov','Ialomița','Iași','Mehedinți','Maramureș','Mureș','Neamț','Olt','Prahova','Sibiu','Sălaj','Satu-Mare','Suceava','Tulcea','Timiș','Teleorman','Vâlcea','Vrancea'];

        foreach($list as $item) {
            DB::table('metadata_counties')->insert([
                'label' => $item,
                'status' => 'active'
            ]);
        }

    }

    public function needTypes() {

        $list = ['Hârtie igienică', 'Măști FPP2', 'Măști FPP3', 'Mănuși'];
        foreach($list as $item) {
            DB::table('metadata_need_types')->insert([
                'label' => $item,
                'status' => 'active'
            ]);
        }

    }
}
