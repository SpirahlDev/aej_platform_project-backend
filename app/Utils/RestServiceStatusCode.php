<?php
namespace App\Utils;

class RestServiceStatusCode {
    // Codes de succès (7000-7999)
    public const SUCCESS_OPERATION = 7000;
    public const SUCCESS_GET_TOKEN = 7001;
    public const SUCCESS_OTP_SENT = 7002;
    public const SUCCESS_ENROLMENT_CODE = 7003;
    public const SUCCESS_SENT_VERIFICATION_EMAIL = 7033;
    public const SUCCESS_PASSWORD_RESET = 7034;

    // Erreurs d'authentification et d'autorisation (8000-8009)
    public const FAILED_OPERATION = 8000;
    public const ERROR_UNAUTHENTICATED = 8001;
    public const SEE_OTHER_REDIRECT = 8003;
    public const ERROR_INVALID_PASSWORD = 8004;
    public const ERROR_INVALID_PARAMETER = 8005;

    // Erreurs liées aux ressources et fichiers (8010-8018)
    public const ERROR_CERTIFICATE_REVOKED = 8011;
    public const ERROR_INVALID_SESSION = 8012;
    public const ERROR_FILE_FORMAT_INVALID = 8013;
    public const ERROR_FILE_EXTENSION_INVALID = 8014;
    public const ERROR_FILE_DOWNLOAD_FAILED_BY_URL = 8015;
    public const ERROR_RESSOURCE_NOT_FOUND = 8016;
    public const VALIDATION_ERROR = 8017;
    public const ERROR_OTP_GENERATION_FAILED = 8018;

    // Erreurs liées aux utilisateurs et ressources (8020-8029)
    public const ERROR_RESSOURCE_ALREADY_EXIST = 8019;
    public const ERROR_OTP_INCORRECT = 8020;
    public const ERROR_DUPLICATE_RESOURCE = 8021;
    public const ERROR_AUTH_BEARER_NOT_FOUND = 8022;
    public const ERROR_AUTH_HEADER_NOT_FOUND = 8023;
    public const ERROR_RESOURCE_NOT_FOUND = 8024;
    public const ERROR_REVOCATION_STATUS_UNCHECKABLE = 8025;
    public const ERROR_DATA_INVALID = 8026;
    public const ERROR_AUTHENTICATION_FAILED = 8027;
    public const ERROR_USER_NOT_FOUND = 8028;
    public const ERROR_USER_ALREADY_EXISTS = 8029;

    // Erreurs CRUD et opérations système (8030-8039)
    public const ERROR_CSRF_TOKEN_NOT_FOUND = 8030;
    public const ERROR_CREATION_FAILED = 8031;
    public const ERROR_DELETION_FAILED = 8032;
    public const ERROR_WRITE_FAILED = 8033;
    public const ERROR_EDIT_FAILED = 8034;
    public const ERROR_LOGIN_ALREADY_EXIST = 8035;
    public const ERROR_PHONE_NUMBER_ALREADY_EXIST = 8036;
    public const ERROR_UPDATE_FAILED = 8037;

    // Erreurs liées à l'OTP (8050-8059)
    public const ERROR_OTP_EXPIRED = 8050;
    public const ERROR_OTP_ATTEMPT_LIMIT_REACHED = 8051;
    public const ERROR_OTP_INVALID = 8052;
    public const ERROR_OTP_REQUEST_LIMIT_REACHED = 8053;

    // Erreurs liées aux tokens (8060-8069)
    public const ERROR_TOKEN_INVALID = 8060;

    // Erreurs liées aux comptes (8088-8090)
    public const ERROR_ACCOUNT_NOT_EXIST = 8088;
    public const ERROR_ACCOUNT_SUSPENDED = 8089;
    public const ERROR_ACCOUNT_NOT_VERIFIED = 8090;

    // Erreurs diverses
    public const ERROR_RATE_LIMIT_EXCEED = 8444;
    public const ERROR_DELETE_FAILED = 8999;

    // Erreurs serveur (9000-9999)
    public const SERVER_ERROR = 9000;
    public const SERVER_ERROR_OTP_GENERATION_FAILED = 9001;
    public const SERVER_ERROR_OTP_SEND_FAILED = 9004;
    public const SERVER_ERROR_CARD_ID_RETRIEVAL_FAILED = 9005;
    public const SERVER_ERROR_INCORRECT_HASH = 9006;
}
