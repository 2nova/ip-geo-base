<?php

namespace Novanova\IPGeoBase;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpgeobaseSeeder extends Seeder
{

    public function run()
    {
        if (file_exists(__DIR__ . '/cities.txt') && file_exists(__DIR__ . '/cidr_optim.txt')) {

            DB::table('ipgeobase_cities')->delete();

            $file = file(__DIR__ . '/cities.txt');
            $pattern = '#(\d+)\s+(.*?)\t+(.*?)\t+(.*?)\t+(.*?)\s+(.*)#';

            DB::beginTransaction();
            foreach ($file as $row) {
                if (preg_match($pattern, $row, $out)) {
                    DB::table('ipgeobase_cities')->insert(
                        array(
                            'id' => $out[1],
                            'city' => $out[2],
                            'region' => $out[3],
                            'district' => $out[4],
                            'lat' => $out[5],
                            'lng' => $out[6],
                            'country' => ''
                        )
                    );
                }
            }
            DB::commit();

            DB::table('ipgeobase_base')->delete();

            $file = file(__DIR__ . '/cidr_optim.txt');
            $pattern = '#(\d+)\s+(\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+-\s+(\d+\.\d+\.\d+\.\d+)\s+(\w+)\s+(\d+|-)#';

            DB::beginTransaction();
            foreach ($file as $row) {
                if (preg_match($pattern, $row, $out)) {
                    DB::table('ipgeobase_base')->insert(
                        array(
                            'long_ip1' => $out[1],
                            'long_ip2' => $out[2],
                            'ip1' => $out[3],
                            'ip2' => $out[4],
                            'country' => $out[5],
                            'city_id' => is_numeric($out[6]) && 0 < (int)$out[6] ? (int)$out[6] : null
                        )
                    );
                }
            }
            DB::commit();

            $cities = DB::table('ipgeobase_cities')
                ->join('ipgeobase_base', 'ipgeobase_cities.id', '=', 'ipgeobase_base.city_id')
                ->select('ipgeobase_cities.id', 'ipgeobase_base.country')->get();

            DB::beginTransaction();
            foreach ($cities as $city) {
                DB::table('ipgeobase_cities')
                    ->where('id', $city->id)
                    ->update(array('country' => $city->country));
            }
            DB::commit();
        }
    }

}