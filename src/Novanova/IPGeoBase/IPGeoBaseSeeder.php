<?php

namespace Novanova\IPGeoBase;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class IPGeoBaseSeeder
 * @package Novanova\IPGeoBase
 */
class IPGeoBaseSeeder extends Seeder
{

    public function run()
    {
        if (file_exists(__DIR__ . '/cities.txt') && file_exists(__DIR__ . '/cidr_optim.txt')) {

            DB::table('ip_geo_base__cities')->delete();

            $file = file(__DIR__ . '/cities.txt');
            $pattern = '#(\d+)\s+(.*?)\t+(.*?)\t+(.*?)\t+(.*?)\s+(.*)#';

            DB::beginTransaction();
            foreach ($file as $row) {
                if (preg_match($pattern, $row, $out)) {
                    DB::table('ip_geo_base__cities')->insert(
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

            DB::table('ip_geo_base__base')->delete();

            $file = file(__DIR__ . '/cidr_optim.txt');
            $pattern = '#(\d+)\s+(\d+)\s+(\d+\.\d+\.\d+\.\d+)\s+-\s+(\d+\.\d+\.\d+\.\d+)\s+(\w+)\s+(\d+|-)#';

            DB::beginTransaction();
            foreach ($file as $row) {
                if (preg_match($pattern, $row, $out)) {
                    DB::table('ip_geo_base__base')->insert(
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

            $cities = DB::table('ip_geo_base__cities')
                ->join('ip_geo_base__base', 'ip_geo_base__cities.id', '=', 'ip_geo_base__base.city_id')
                ->select('ip_geo_base__cities.id', 'ip_geo_base__base.country')->get();

            DB::beginTransaction();
            foreach ($cities as $city) {
                DB::table('ip_geo_base__cities')
                    ->where('id', $city->id)
                    ->update(array('country' => $city->country));
            }
            DB::commit();
        }
    }

}