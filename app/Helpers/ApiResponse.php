<?php

namespace App\Helpers;

use App\Utils\HttpStatusCodes;
use App\Utils\RestServiceStatusCode;
use Illuminate\Http\JsonResponse;

class ApiResponse{

    const SUCCESS_MESSAGE='Operation Successful';

    private static int $statusCode=0;
    private static string $statusMessage="";
    private static $data;
    private static array $metaData=[];

    public static function setContextStatusCode(int $statusCode){
        self::$statusCode=$statusCode;
        return new self;
    }

    public static function setStatusMessage(string $message){
        self::$statusMessage=$message;
        return new self;

    }

    public static function setData($data){
        self::$data=$data;
        return new self;

    }

    public static function setHeader(array $header){
        self::$metaData=$header;
    }

    public static function sendToClient(int $httpStatus=200){
        return response()->json([
            'status_code' => self::$statusCode,
            'status_message' => self::$statusMessage,
            'data' => self::$data
        ],$httpStatus)->header('Content-Type', 'application/json');
    }



    public static function respond($message="",int $contextStatusCode=RestServiceStatusCode::SUCCESS_OPERATION,int $httpStatus=200,$data=""){

        $jsonBody=[
            'status_code' => $contextStatusCode,
            'status_message' =>  $message,
            'data' => $data,
        ];
        if(!empty(self::$metaData)){
            $jsonBody['meta_data'] = self::$metaData;
        }
        return response()->json($jsonBody,$httpStatus,self::$metaData)->header('Content-Type', 'application/json');
    }

    public static function redirect(string $redirect_url,$message=""){
        return response()->json([
            'status_code' => RestServiceStatusCode::SEE_OTHER_REDIRECT,
            'redirect_url'=>$redirect_url,
            'status_message' => $message,
        ],HttpStatusCodes::HTTP_OK)->header('Content-Type', 'application/json');
    }

    public static function setMetaData(array $metaData){
        self::$metaData=$metaData;
        return new self;
    }

    public static function tellCriticalError(){
            return self::respond('An unknown error has occurred.',RestServiceStatusCode::SERVER_ERROR,HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function success($data="",$message="",$status=200){
        $jsonBody=[
            'status_code' => RestServiceStatusCode::SUCCESS_OPERATION,
            'status_message' =>  "Opération réussie",
            'data' => $data,
        ];
        if(!empty(self::$metaData)){
            $jsonBody['meta_data'] = self::$metaData;
        }
        return response()->json($jsonBody,$status,self::$metaData)->header('Content-Type', 'application/json');
    }

    public static function fail($data="",$message=""){
        $jsonBody=[
            'status_code' => RestServiceStatusCode::FAILED_OPERATION,
            'status_message' =>  "Opération échouée",
            'data' => $data,
        ];
        if(!empty(self::$metaData)){
            $jsonBody['meta_data'] = self::$metaData;
        }
        return response()->json($jsonBody,200,self::$metaData)->header('Content-Type', 'application/json');
    }
}
