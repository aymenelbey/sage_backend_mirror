<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'Commune' => 'App\Models\Commune',
            'Syndicat' => 'App\Models\Syndicat',
            'Epic' => 'App\Models\EPIC',
            'EPIC' => 'App\Models\EPIC',
            'Societe' => 'App\Models\SocieteExploitant',
            'TRI' => 'App\Models\DataTechnTRI',
            'TMB' => 'App\Models\DataTechnTMB',
            'ISDND' => 'App\Models\DataTechnISDND',
            'UVE' => 'App\Models\DataTechnUVE',
        ]);
    }
}