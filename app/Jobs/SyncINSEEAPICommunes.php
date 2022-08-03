<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Commune;

class SyncINSEEAPICommunes implements ShouldQueue
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
        echo "Syncing communes";
        if($this->action == 'sync_all'){
            echo "Syncing all communes";
            $total = 0;
            Commune::chunk(100, function($communes) use (&$total){
                $sirens = [];
                foreach($communes as $commune){ 
                    $sirens[] = $commune->serin;
                }
                $total += sizeof($sirens);
                Commune::sync_api($this->token, $sirens);
            });
            echo "Total Synced ".$total;
        }else if($this->action == 'sync_file'){
            return true;
        }
    }
}
