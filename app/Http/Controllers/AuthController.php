<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AuthService;
use App\Utils\HttpStatusCodes;
use App\Utils\RestServiceStatusCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class AuthController
{


    /**
     * Constructor avec injection du service
     */
    public function __construct(private AuthService $authService){}



    public function login(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => ['required'],
        ]);

        $account=$this->authService->getAccountByEmail($credentials['email']);

        if(!$account){
            return ApiResponse::respond("Compte introuvable",
                RestServiceStatusCode::ERROR_RESSOURCE_NOT_FOUND,
                HttpStatusCodes::HTTP_NOT_FOUND);
        }

//        dd($account->getPassword());
        if (Auth::validate($credentials)) {
            $account->update(['last_login' => now()]);
            Auth::login($account);
            return ApiResponse::respond('Connexion réussi',
                RestServiceStatusCode::SUCCESS_OPERATION,
                HttpStatusCodes::HTTP_OK);
        }else{
            return ApiResponse::respond('Connexion échouée',
                RestServiceStatusCode::FAILED_OPERATION,
                HttpStatusCodes::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Affiche la liste des comptes.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer le terme de recherche s'il existe
        $searchTerm = $request->input('search');

        if ($searchTerm) {
            $accounts = $this->authService->searchPaginatedAccounts($searchTerm);
        } else {
            $accounts = $this->authService->getPaginatedAccounts();
        }

        return view('accounts.index', [
            'accounts' => $accounts,
            'searchTerm' => $searchTerm
        ]);
    }



    /**
     * API - Récupère tous les comptes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAll()
    {
        $accounts = $this->authService->getAllAccounts();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    /**
     * API - Récupère tous les comptes par profil.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetByProfile(Request $request)
    {
        $profileName = $request->input('profile');

        if (!$profileName) {
            return response()->json([
                'success' => false,
                'message' => 'Le paramètre profile est requis'
            ], 400);
        }

        $accounts = $this->authService->getAccountsByProfile($profileName);

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }


}
