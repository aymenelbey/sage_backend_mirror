<?php

namespace App\Jobs\Export;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\ExportNotification;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Throwable;

abstract class ExportDefault implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path = "app/exports";
    protected $chunks = 1000;
    protected $user, $title, $failed_action, $filepath;

    public function __construct($user, $title, $failed_action)
    {
        $this->user = $user;
        $this->title = $title;
        $this->failed_action = $failed_action;
    }

    public function handle()
    {
        $filename = uniqid() . "_" . str_replace(" ", "_", $this->title) . "_" . date("h-i-s_j-m-y") . ".xlsx";
        $today = date("j-m-y");
        $path_today = "$this->path/$today";
        $path_today_filename = "$path_today/$filename";

        $this->filepath = "$today/$filename";

        if (!file_exists(storage_path($path_today))) mkdir(storage_path($path_today));

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile(storage_path($path_today_filename));

        $this->job($writer);

        $writer->close();
        
        $this->succeeded();
    }

    public function succeeded() {
        $this->user->notify(new ExportNotification([
            "title" => "L'exportation des " . $this->title . " a réussi",
            "description" => "subDescData",
            "logo" => "/media/svg/icons/Costum/ImportSuccess.svg",
            "action" => env("APP_HOTS_URL") . "exports/" . $this->filepath,
        ]));
    }

    public function failed(Throwable $exception)
    {
        $this->user->notify(new ExportNotification([
            "title" => "L'exportation des " . $this->title . " a échoué",
            "description" => "subDescData",
            "logo" => "/media/svg/icons/Costum/WarningReqeust.svg",
            "action" => $this->failed_action
        ]));
    }
}
