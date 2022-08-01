<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EPIC;


class SyncINSEEAPIEPICs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    protected $action;
    protected $filepath;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token, $action, $filepath = null, $user = null){
        $this->token=$token;
        $this->action=$action;
        $this->filepath=$filepath;
        $this->user=$user;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        echo "Syncing EPICs";
        if($this->action == 'sync_all'){
            echo "Syncing all EPICs";
            $total = 0;
            EPIC::chunk(100, function($societies) use (&$total){
                $sirens = [];
                foreach($societies as $societie){ 
                    $sirens[] = $societie->serin;
                }
                $total += sizeof($sirens);
                EPIC::sync_api($this->token, $sirens);
            });
            echo "Total Synced ".$total;
        }else if($this->action == 'sync_file'){
            return true;
        }
    }
}
