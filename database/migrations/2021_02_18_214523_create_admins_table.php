<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Admin;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements("id_admin");
            $table->string("nom",100)->nullable();
            $table->string("prenom",100)->nullable();
            $table->string('email')->unique()->nullable();
            $table->foreignId('id_user')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
        $user = User::create([
            "username"=>"sage_admin",
            "typeuser"=>"SupAdmin",
            "password"=>Hash::make("123456789")
        ]);
        $admin = Admin::create([
            "id_user"=>$user->id,
            "nom"=>"Admin",
            "prenom"=>"Sage",
            "email"=>"z.khedri@sobiapi.com"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}