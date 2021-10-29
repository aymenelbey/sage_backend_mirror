<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShareSite;
use App\Models\Admin;
use App\Models\UserPremieum;
use JWTAuth;
use Carbon\Carbon;

class ShareSiteController extends Controller
{
    CONST VALID_COLOMNS=["denomination","categorieSite","adresse","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev","quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite", "capaciteHoraire","capaciteNominale","dernierConstructeur","extension",'nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation"];
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
                            "columns"=>$dataShare['columns'],
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
    public function handle_share(Request $request){
        $share=ShareSite::find($request['idShare']);
        if($share){
            ShareSite::where('id_share_site',$request['idShare'])->update([
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['shares']) && is_array($request['shares'])){
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
                'data'=>"async"
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
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
            $share->columns=$request["columns"];
            $columns=$request["columns"];
        }
        $share->save();
        $share->columns=$columns;
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