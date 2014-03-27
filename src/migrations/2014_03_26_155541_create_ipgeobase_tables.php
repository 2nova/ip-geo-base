<?php

use Illuminate\Database\Migrations\Migration;

class CreateIpgeobaseTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ip_geo_base__cities',
            function ($table) {
                $table->increments('id')->unsigned();
                $table->string('city', 128);
                $table->string('region', 128);
                $table->string('district', 128);
                $table->string('country', 2);
                $table->float('lat');
                $table->float('lng');
                $table->index('country');
            }
        );
        Schema::create(
            'ip_geo_base__base',
            function ($table) {
                $table->increments('id')->unsigned();
                $table->bigInteger('long_ip1')->unsigned();
                $table->bigInteger('long_ip2')->unsigned();
                $table->string('ip1', 16);
                $table->string('ip2', 16);
                $table->string('country', 2);
                $table->integer('city_id')->unsigned()->nullable()->default(null);
                $table->index(array('long_ip1', 'long_ip2'));
                $table->foreign('city_id')
                    ->references('id')->on('ip_geo_base__cities')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ip_geo_base__base');
        Schema::drop('ip_geo_base__cities');
    }

}