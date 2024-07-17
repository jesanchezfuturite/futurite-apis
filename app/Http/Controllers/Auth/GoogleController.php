<?php

namespace App\Http\Controllers\Auth;
  
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
  
class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
        
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        try {
            //usuario autenticado con google
            $user = Socialite::driver('google')->user();

            //usuario local
            $finduser = User::Where('email', $user->email)->first();
       
            //si el usuario existe, se inicia la sesion
            if($finduser){
                // $finduser->avatar = $this->downloadAvatar($user);
                // $finduser->google_id = $user->id;
                // $finduser->save();
       
                Auth::login($finduser);
      
                return redirect()->intended('home');
       
            }else{
                return redirect()->intended('home');
            }
      
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Descarga el Avatar de la cuenta de google con la que se inicia sesion
     * @user => cuenta de usuario google
     */
    private function downloadAvatar($user){
        $avatar_name = null;
        if(!empty($user->avatar)){
            $response = Http::get($user->avatar);
            if($response->successful()){
                $avatar_name = $user->id . '_avatar.png';
                Storage::disk('local')->put('public/avatares/'.$avatar_name, $response);
            }
        }
        
        return $avatar_name;
    }
}