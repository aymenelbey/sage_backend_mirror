<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Imports\CollectionsImport;
use App\Exports\CollectionsExport;

use Maatwebsite\Excel\Facades\Excel;

use App\models\Enemuration;
use App\models\Commune;
use App\models\Departement;
use App\models\Contact;
use App\models\ContactHasPersonMoral;
use App\models\PersonFunction;
use App\models\EPIC;
use App\models\Site;
use App\models\SocieteExploitant;
use App\models\Syndicat;
use Carbon\Carbon;

use App\Http\Helpers\ToolHelper;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::get('sage/test', [App\Http\Controllers\CommuneController::class, "sync_api"]);
Route::get('sage/test', [App\Http\Controllers\SocieteExploitantController::class, "sync_api"]);

Route::post("start_sync_batch/syndicat_sync_insee",[App\Http\Controllers\SyndicatController::class,"sync_api"]);
Route::post("start_sync_batch/communes_sync_insee",[App\Http\Controllers\CommuneController::class,"sync_api"]);
Route::post("start_sync_batch/societies_sync_insee",[App\Http\Controllers\SocieteExploitantController::class,"sync_api"]);
Route::post("start_sync_batch/epics_sync_insee",[App\Http\Controllers\EPICController::class,"sync_api"]);



Route::get("data/download/{type}", [App\Http\Controllers\ImportData::class, "download_update_file"]);

Route::post('/users/send-email', [App\Http\Controllers\auth\ForgotPasswordController::class,"forgot"]);
Route::post('/login', [App\Http\Controllers\auth\LoginController::class,"login"]);
Route::post('/create/admin', [App\Http\Controllers\auth\LoginController::class,"createAdmin"]);
Route::middleware('auth:api')->group(function () {
    Route::get("notifications",[App\Http\Controllers\UserController::class,"notifications"]);
    Route::patch("notifications/read/{id}",[App\Http\Controllers\UserController::class,"read_notification"]);
    Route::post("user/update/picture",[App\Http\Controllers\UserController::class,"updatePicture"]);
    Route::patch("user/update",[App\Http\Controllers\UserController::class,"updateUser"]);
    Route::get('departement/list', [App\Http\Controllers\DepartementController::class,"index"]);
    Route::get('region/list', [App\Http\Controllers\RegionController::class,"index"]);
    Route::get('localization/all', [App\Http\Controllers\CommonActionsController::class,"localization"]);
    Route::get('info/me', [App\Http\Controllers\auth\LoginController::class,"user"]);
    Route::patch('password/update', [App\Http\Controllers\UserController::class,"updatePassword"]);
    Route::middleware(['premission:SupAdmin'])->group(function(){
        Route::prefix("enums/")->group(function(){
            Route::post("create",[App\Http\Controllers\EnemurationController::class,"create"]);
            Route::delete("delete",[App\Http\Controllers\EnemurationController::class,"destroy"]);
        });
        Route::prefix("admins/")->group(function(){
            Route::post("create",[App\Http\Controllers\AdminController::class,"create"]);
            Route::get("show",[App\Http\Controllers\AdminController::class,"index"]);
            Route::delete("destroy",[App\Http\Controllers\AdminController::class,"destroy"]);
            Route::patch("update",[App\Http\Controllers\AdminController::class,"update"]);
        });
    });
    Route::middleware(['premission:Admin'])->group(function(){
        Route::get('history/fetch',[App\Http\Controllers\InfoHistoryController::class,'fetchHistory']);
        Route::post("data/import",[App\Http\Controllers\ImportData::class,"import"]);
        Route::prefix("departement/")->group(function(){
            Route::get('enums', [App\Http\Controllers\DepartementController::class,"fetch_list"]);
            Route::post('add', [App\Http\Controllers\DepartementController::class,"create"]);
            Route::delete('delete', [App\Http\Controllers\DepartementController::class,"soft_delete"]);
        });
        Route::prefix("region/")->group(function(){
            Route::get('enums', [App\Http\Controllers\RegionController::class,"fetch_list"]);
            Route::post('add', [App\Http\Controllers\RegionController::class,"create"]);
            Route::delete('delete', [App\Http\Controllers\RegionController::class,"soft_delete"]);
        });
        Route::prefix("shareds/")->group(function(){
            Route::post("add",[App\Http\Controllers\ShareSiteController::class,"share"]);
            Route::get("all",[App\Http\Controllers\ShareSiteController::class,"index"]);
            Route::patch("status",[App\Http\Controllers\ShareSiteController::class,"handle_share"]);
            Route::patch("extend",[App\Http\Controllers\ShareSiteController::class,"extend_site"]);
            Route::delete("delete",[App\Http\Controllers\ShareSiteController::class,"destroy"]);
        });
        Route::prefix("map/")->group(function(){
            Route::get("{lat}/{lang}",[App\Http\Controllers\MapSitesController::class,"getSites"]);
        });
        Route::prefix('sites/')->group(function () {
            Route::get("show/{id_site}",[App\Http\Controllers\SiteController::class,"show"]);
            Route::delete("delete",[App\Http\Controllers\SiteController::class,"destroy"]);
            Route::get("all",[App\Http\Controllers\SiteController::class,"all"]);
            Route::post("create",[App\Http\Controllers\SiteController::class,"create"]);
            Route::patch("update",[App\Http\Controllers\SiteController::class,"update"]);
            Route::get("edit/{id_site}",[App\Http\Controllers\SiteController::class,"edit"]);
            Route::get('export', [App\Http\Controllers\SiteController::class, "export"]);
        });
        Route::prefix("gestionnaire/")->group(function(){
            Route::post("create",[App\Http\Controllers\GestionnaireController::class,"create"]);
            Route::get("all",[App\Http\Controllers\GestionnaireController::class,"all"]);
            Route::patch("update",[App\Http\Controllers\GestionnaireController::class,"update"]);
            Route::get("edit/{idgestionnaire}",[App\Http\Controllers\GestionnaireController::class,"edit"]);
            Route::get("show/{idgestionnaire}",[App\Http\Controllers\GestionnaireController::class,"show"]);
            Route::delete("delete",[App\Http\Controllers\GestionnaireController::class,"destroy"]);
            Route::get("sites/{idgestionnaire}",[App\Http\Controllers\GestionnaireController::class,"show_sites"]);
            Route::delete("sites/remove",[App\Http\Controllers\GestionnaireController::class,"destroy_sites"]);
        });
        Route::prefix("usersimple/")->group(function(){
            Route::post("create",[App\Http\Controllers\UserSimpleController::class,"create"]);
            Route::patch("update",[App\Http\Controllers\UserSimpleController::class,"update"]);
            Route::get("fetch",[App\Http\Controllers\UserSimpleController::class,"index"]);
        });
        Route::prefix("premieums/")->group(function(){
            Route::post("create",[App\Http\Controllers\UserPremieumController::class,"create"]);
            Route::get("all",[App\Http\Controllers\UserPremieumController::class,"all"]);
            Route::get("show/{idUser}",[App\Http\Controllers\UserPremieumController::class,"show"]);
            Route::patch("update",[App\Http\Controllers\UserPremieumController::class,"update"]);
            Route::delete("delete",[App\Http\Controllers\UserPremieumController::class,"destroy"]);
            Route::get("sites/{idUserPrem}",[App\Http\Controllers\UserPremieumController::class,"show_sites"]);
            Route::get("sessions/{idUserPrem}",[App\Http\Controllers\UserPremieumController::class,"show_sessions"]);
            Route::delete("sessions/delete",[App\Http\Controllers\UserPremieumController::class,"delete_session"]);
        });
        Route::prefix("societe/")->group(function(){
            Route::post("create",[App\Http\Controllers\SocieteExploitantController::class,"create"]);
            Route::get("all",[App\Http\Controllers\SocieteExploitantController::class,"index"]);
            Route::get("edit/{idSociete}",[App\Http\Controllers\SocieteExploitantController::class,"edit"]);
            Route::get("show/{idcompany}",[App\Http\Controllers\SocieteExploitantController::class,"show"]);
            Route::delete("delete",[App\Http\Controllers\SocieteExploitantController::class,"destroy"]);
            Route::patch("updateSoc",[App\Http\Controllers\SocieteExploitantController::class,"update"]);
            Route::post("sync_insee",[App\Http\Controllers\SocieteExploitantController::class,"sync_api"]);
        });
        Route::prefix("clients/")->group(function(){
            Route::get("list",[App\Http\Controllers\CollectiviteController::class,"index"]);
            Route::prefix("communs/")->group(function(){
                Route::post("create",[App\Http\Controllers\CommuneController::class,"create"]);
                Route::get("all",[App\Http\Controllers\CommuneController::class,"all"]);
                Route::get("show/{idcommune}",[App\Http\Controllers\CommuneController::class,"show"]);
                Route::get("edit/{idcommune}",[App\Http\Controllers\CommuneController::class,"edit"]);
                Route::delete("delete",[App\Http\Controllers\CommuneController::class,"destroy"]);
                Route::patch("update",[App\Http\Controllers\CommuneController::class,"update"]);
                Route::post("sync_insee",[App\Http\Controllers\CommuneController::class,"sync_api"]);
            });
            Route::prefix("epics/")->group(function(){
                Route::post("create",[App\Http\Controllers\EPICController::class,"create"]);
                Route::get("all",[App\Http\Controllers\EPICController::class,"all"]);
                Route::get("show/{idepic}",[App\Http\Controllers\EPICController::class,"show"]);
                Route::get("edit/{idepic}",[App\Http\Controllers\EPICController::class,"edit"]);
                Route::delete("delete",[App\Http\Controllers\EPICController::class,"destroy"]);
                Route::patch("update",[App\Http\Controllers\EPICController::class,"update"]);
                Route::post("sync_insee",[App\Http\Controllers\EPICController::class,"sync_api"]);
            });
            Route::prefix("syndicats/")->group(function(){
                Route::post("create",[App\Http\Controllers\SyndicatController::class,"create"]);
                Route::get("all",[App\Http\Controllers\SyndicatController::class,"all"]);
                Route::get("show/{idSyndicat}",[App\Http\Controllers\SyndicatController::class,"show"]);
                Route::get("edit/{idSyndicat}",[App\Http\Controllers\SyndicatController::class,"edit"]);
                Route::delete("delete",[App\Http\Controllers\SyndicatController::class,"destroy"]);
                Route::patch("update",[App\Http\Controllers\SyndicatController::class,"update"]);
                Route::post("sync_insee",[App\Http\Controllers\SyndicatController::class,"sync_api"]);

            });
            Route::post("add/site",[App\Http\Controllers\CollectiviteController::class,"add"]);
            Route::get("all",[App\Http\Controllers\CollectiviteController::class,"index"]);
            Route::get("sites/{idClient}",[App\Http\Controllers\CollectiviteController::class,"sitesByClient"]);
        });
        Route::prefix("contrat/")->group(function(){
            Route::post("create",[App\Http\Controllers\ContratController::class,"create"]);
            Route::get("all",[App\Http\Controllers\ContratController::class,"index"]);
            Route::delete("delete",[App\Http\Controllers\ContratController::class,"destroy"]);
            Route::get("show/{idContract}",[App\Http\Controllers\ContratController::class,"show"]);
            Route::get("edit/{idContract}",[App\Http\Controllers\ContratController::class,"edit"]);
            Route::patch("update",[App\Http\Controllers\ContratController::class,"update"]);
        });
        Route::prefix("ged/")->group(function(){
            
            Route::post("create",[App\Http\Controllers\GEDController::class,"create"]);
            Route::get("all",[App\Http\Controllers\GEDController::class,"show"]);

            Route::get("show/{file_id}",[App\Http\Controllers\GEDController::class,"getGEDFile"]);
            
            Route::delete("delete",[App\Http\Controllers\GEDController::class,"destroy"]);
            Route::get("show/{idContract}",[App\Http\Controllers\GEDController::class,"show"]);
            Route::get("edit/{idContract}",[App\Http\Controllers\GEDController::class,"edit"]);
            Route::patch("update",[App\Http\Controllers\GEDController::class,"update"]);
        });
        Route::prefix("contacts/")->group(function(){
            Route::post("create",[App\Http\Controllers\ContactController::class,"create"]);
            Route::get("all",[App\Http\Controllers\ContactController::class,"index"]);
            Route::get("show/{id_contact}",[App\Http\Controllers\ContactController::class,"show"]);
            Route::get("edit/{id_contact}",[App\Http\Controllers\ContactController::class,"edit"]);
            Route::delete("delete",[App\Http\Controllers\ContactController::class,"destroy"]);
            Route::patch("update",[App\Http\Controllers\ContactController::class,"update"]);
            Route::delete("function/delete",[App\Http\Controllers\ContactController::class,"delete_function"]);
            Route::patch("function/status",[App\Http\Controllers\ContactController::class,"handle_function"]);
        });
    });
    Route::middleware(['premission:UserPremieume'])->group(function(){
        Route::prefix("user/")->group(function(){
            Route::get("sessions/fetch",[App\Http\Controllers\Users\SessionsUserController::class,"index"]);
            Route::post("sessions/create",[App\Http\Controllers\Users\SessionsUserController::class,"create"]);
            Route::patch("sessions/update",[App\Http\Controllers\Users\SessionsUserController::class,"update"]);
        });
    });
    Route::middleware(['premission:UserSimple'])->group(function(){
        Route::prefix("user/")->group(function(){
            Route::get("sites/list/{lat}/{lang}",[App\Http\Controllers\Users\UserSitesController::class,"show_sites"]);
            Route::get("share/detail/{idShare}/{idSite}",[App\Http\Controllers\Users\UserSitesController::class,"show_detail"]);
        });
    });
    Route::middleware(['premission:Gestionnaire'])->group(function(){
        Route::post("move/file",[App\Http\Controllers\CommonActionsController::class,"move_file"]);
        Route::get("enums/show",[App\Http\Controllers\EnemurationController::class,"index"]);
        Route::prefix("managers/sites/")->group(function(){
            Route::get("all",[App\Http\Controllers\GestionnaireController::class,"list_sites"]);
            Route::middleware(['gestionnairePerm:idSite'])->group(function(){
                Route::get("show/{id_site}",[App\Http\Controllers\SiteController::class,"show"]);
                Route::get("edit/{id_site}",[App\Http\Controllers\SiteController::class,"edit"]);
            });
            Route::delete("delete",[App\Http\Controllers\SiteController::class,"destroy"])->middleware(['gestionnairePerm:delete']);
            Route::patch("update",[App\Http\Controllers\SiteController::class,"update"])->middleware(['gestionnairePerm:update']);
        });
        Route::prefix("managers/map/")->group(function(){
            Route::get("{lat}/{lang}",[App\Http\Controllers\MapSitesController::class,"getSites_manager"]);
        });
    });
});