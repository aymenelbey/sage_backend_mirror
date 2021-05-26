<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShareSite;
use App\Models\Admin;
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
            'checkedColumns' => 'required|array',
            'site' => 'required|integer|exists:sites,id_site',
            'dateDebut' => 'required',
            'dateFin' => 'required',
            "userPrem"=>'required|integer|exists:user_premieums,id_user_premieum'
        ],[
            'checkedColumns.required'=>"Les colonnes à partager sont obligatoire",
            "dateDebut.required"=>"La date debut de partager est obligatoire",
            "dateFin.required"=>"La date fin de partager est obligatoire",
            "userPrem.required"=>"L'utilisateur est obligatoire",
            "site.required"=>"Le site à partager est obligatoire",
            "site.exists"=>"Le site doit etre un site valid",
            "userPrem.exists"=>"L'utilisateur doit etre un utilisateur valid",
        ]);
        $colons='';
        foreach($request['checkedColumns'] as $key=>$value){
            if(in_array($key,self::VALID_COLOMNS) && $value){
                $colons.=$key.'|';
            }
        }
        $user = JWTAuth::user();
        $admin = Admin::where("id_user","=",$user->id)->first();
        $siteToShare=ShareSite::create([
            "start"=>Carbon::createFromFormat('d/m/Y', $request->dateDebut)->format('Y-m-d'),
            "end"=>Carbon::createFromFormat('d/m/Y', $request->dateFin)->format('Y-m-d'),
            "columns"=>$colons,
            "id_user_premieum"=>$request['userPrem'],
            "id_site"=>$request['site'],
            "id_admin"=>$admin->id_admin
        ]);
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
            $share->is_blocked=!$share->is_blocked;
            $share->save();
            $share->site;
            $share->start=Carbon::parse($share->start)->format('d/m/y');
            $share->end=Carbon::parse($share->end)->format('d/m/y');
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
        $share->start=Carbon::createFromFormat('d/m/Y', $request->start)->format('Y-m-d');
        $share->end=Carbon::createFromFormat('d/m/Y', $request->end)->format('Y-m-d');
        if(isset($request["columns"]) && is_array($request["columns"])){
            $columns='';
            foreach($request['columns'] as $key=>$value){
                if(in_array($key,self::VALID_COLOMNS) && $value){
                    $columns.=$key.'|';
                }
            }
            $share->columns=$columns;
        }
        $share->save();
        $share->site;
        $share->start=Carbon::parse($share->start)->format('d/m/y');
        $share->end=Carbon::parse($share->end)->format('d/m/y');
        return response([
            'ok'=>true,
            "data"=>$share
        ]);
    }
}