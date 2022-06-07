<?php

namespace App\Http\Controllers;

use App\Http\Helpers\SiteHelper;
use Illuminate\Http\Request;
use App\Models\ShareSite;
use App\Models\Admin;
use App\Models\UserPremieum;
use JWTAuth;
use Carbon\Carbon;

class ShareSiteController extends Controller
{
    CONST VALID_COLOMNS=["denomination","categorieSite","adresse","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev","quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite", "capaciteHoraire","capaciteNominale","dernierConstructeur","extension","nombreFours","capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation","denomination","modeGestion","categorieSite","adresse","siteIntrnet","telephoneStandrad","anneeCreation","perdiocitRelance","client_nomEpic","client_nom_court","client_serin","client_siteInternet","client_telephoneStandard","client_nature_juridique","client_nomCourt","client_denominationLegale","client_sinoe","client_amobe","client_nomCommune","client_insee","client_adresse","client_city","client_country","client_postcode","client_region_siege","client_departement_siege","client_nombreHabitant","company_groupe","company_denomination","company_serin","company_sinoe","company_nature_juridique","company_codeape","company_siteInternet","company_telephoneStandard","company_effectifs","company_adresse","company_city","company_country","company_postcode"];
    /**
     * display all shares.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

    }
    /**
     * Share a site to the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function share(Request $request){
        $this->validate($request, [
            'dataShare' => ['required','array'],
            'dateDebut' => 'required',
            'dateFin' => 'required',
            "userPrem"=>['required','array'],
        ]);
        $user = JWTAuth::user();
        $admin = Admin::where("id_user","=",$user->id)->first();
        foreach($request->userPrem as $user){
            $userPrem=UserPremieum::find($user);
            if($userPrem){
                foreach($request->dataShare as $dataShare){
                    if(in_array($dataShare['typeShare'],['Site','Departement','Region'])){
                        $siteToShare=ShareSite::create([
                            "start"=>Carbon::createFromFormat('d/m/Y', $request->dateDebut)->format('Y-m-d'),
                            "end"=>Carbon::createFromFormat('d/m/Y', $request->dateFin)->format('Y-m-d'),
                            "columns"=> SiteHelper::prepareCols($dataShare['typeShare'], $dataShare['columns'], true),
                            "files" => $dataShare['files'],
                            "id_user_premieum"=>$user,
                            "id_data_share"=>$dataShare['dataShare'],
                            "type_data_share"=>$dataShare['typeShare'],
                            "id_admin"=>$admin->id_admin
                        ]);
                    }
                }
            }
        }
        return response([
            'ok'=>true,
            "data"=>$siteToShare
        ]);
    }
    /**
        * Share a site to the specified resource in storage.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
    */
    public function hnadle_update_share($request){

        $sites = ShareSite::where([['id_user_premieum', $request['id_user_premieum']], ['type_data_share', $request['type_data_share']]]);


        switch($request['action']){
            case 'pause-all':
            case 'unpause-all':
                $sites->update(['is_blocked' => $request['action'] == 'pause-all' ? true : false]);
                return response([
                    "ok" => true,
                    "request" => $request->input(), 
                    "message"=> $request['action'] == 'pause-all' ? "All paused" : 'All unpaused'
                ], 200);
            case 'update-dates':
                $sites->update(["start" => Carbon::createFromFormat('d/m/Y', $request['start_date'])->format('Y-m-d'), "end" => Carbon::createFromFormat('d/m/Y', $request['end_date'])->format('Y-m-d')]);
                return response([
                    "ok" => true,
                    "request" => $request->input(),
                    "message"=> "Shares updated"
                ], 200);
                break;
        }

        return response([
            "message"=> "sites updated",
            "ok" => true
        ],400);

    }
    public function handle_share(Request $request){
        switch($request['action']){
            case 'update-status-one':
                $share = ShareSite::find($request['idShareSite']);        
                if($share){
                    ShareSite::where('id_share_site',$request['idShareSite'])->update([
                        'is_blocked'=>!$share->is_blocked
                    ]);
                    $share->is_blocked=!$share->is_blocked;
                    switch($share->type_data_share){
                        case "Site":
                            $share->site;
                            break;
                        case "Departement":
                            $share->departement;
                            break;
                        case "Region":
                            $share->region;
                            break;
                    }
                    $share->start=Carbon::parse($share->start)->format('d/m/Y');
                    $share->end=Carbon::parse($share->end)->format('d/m/Y');
                    return response([
                        "ok"=>true,
                        "data"=>$share
                    ],200);
                }
                return response([
                    "message"=>"Failed to get site"
                ],400);
                break;
            case 'pause-all':
            case 'unpause-all':
            case 'update-dates':
                return $this->hnadle_update_share($request);
            default:
                break;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['id_user_premieum']) && !empty($request['id_user_premieum']) && isset($request['type_data_share']) && !empty($request['type_data_share'])){
            
            ShareSite::where('id_user_premieum', $request['id_user_premieum'])->where('type_data_share', $request['type_data_share'])->delete();
            return response([
                'ok' => true,
                'data' => "Deleted"
            ]);

        }else if(isset($request['shares']) && is_array($request['shares'])){
            $deletedLis=[];
            foreach($request['shares'] as $share){
                $shr=ShareSite::find($share);
                if($shr){
                    $deletedLis [] = $share;
                    $shr->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                "request" => $request->input()
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
    public function extend_site_dates($request){

        $share->start=Carbon::createFromFormat('d/m/Y', $request->start)->format('Y-m-d');
        $share->end=Carbon::createFromFormat('d/m/Y', $request->end)->format('Y-m-d');

    }
    public function extend_site(Request $request){

        $this->validate($request,[
            "share"=>['required','exists:share_sites,id_share_site'],
            "start"=>["required"],
            "end"=>['required']
        ],[
            "share.exists"=>["Le partage que voullez renouveller n'exits pas"],
            "start.required"=>['La nouvelle date début de partage et obligatoire'],
            "end.required"=>['La nouvelle date fin de partage et obligatoire']
        ]);
        $share=ShareSite::find($request['share']);
        $columns=$share->columns;
        $share->start=Carbon::createFromFormat('d/m/Y', $request->start)->format('Y-m-d');
        $share->end=Carbon::createFromFormat('d/m/Y', $request->end)->format('Y-m-d');
        if(isset($request["columns"]) && is_array($request["columns"])){
            $share->columns= SiteHelper::prepareCols($share->type_data_share, $request['columns'], true);
        }
        if(isset($request["files"]) && is_array($request["files"])){
            $share->files = $request['files'];
        }
        $share->save();
        $share->columns = SiteHelper::explodeCols($share->type_data_share, $share->columns);

        switch($share->type_data_share){
            case "Site":
                $share->site;
                break;
            case "Departement":
                $share->departement;
                break;
            case "Region":
                $share->region;
                break;
        }
        $share->start=Carbon::parse($share->start)->format('d/m/Y');
        $share->end=Carbon::parse($share->end)->format('d/m/Y');
        return response([
            'ok'=>true,
            "data"=>$share
        ]);
    }
}