<?php

namespace App\Utils;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Utils{

    public static function formatFileSize($bytes, $precision = 2) : string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $size = $bytes / (1024 ** $power);

        return round($size, $precision) . ' ' . $units[$power];
    }

    public static function getSize(UploadedFile $file, string $unit = 'B', int $precision = 3): float
    {
        $size = $file->getSize();

        // Utilisation de la base 1024 (binaire)
        $units = [
            'B'   => 1,
            'KB' => 1024,
            'MB' => 1024 ** 2,
            'GB' => 1024 ** 3,
            'TB' => 1024 ** 4,
        ];

        // Vérifier si l'unité demandée est supportée
        if (!array_key_exists($unit, $units)) {
            throw new \InvalidArgumentException(
                "L'unité spécifiée n'est pas valide. Utilisez 'B', 'KiB', 'MiB', 'GiB' ou 'TiB'."
            );
        }

        // Vérifier que la précision est valide
        if ($precision < 0) {
            throw new \InvalidArgumentException("La précision doit être un nombre positif.");
        }

        // Calcul de la taille dans l'unité spécifiée
        $convertedSize = $size / $units[$unit];

        return round($convertedSize, $precision);
    }

    public static function generatePassword(int $length=8){
        $length=$length<8?8:$length;
        $pwd=Str::random($length-3);

        $pwd.=strtolower(Str::random(1));
        $pwd.=strtoupper(Str::random(1));
        $pwd.=random_int(0,9);


        return  str_shuffle($pwd);
    }

    public static function validatePassword($password) {
        $errors = [];

        // Vérification longueur
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit faire au moins 8 caractères";
        }
        if (strlen($password) > 16) {
            $errors[] = "Le mot de passe ne doit pas dépasser 16 caractères";
        }

        // Majuscules
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule";
        }

        // Minuscules
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule";
        }

        // Chiffres
        if (!preg_match('/\d/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }

        // Caractères spéciaux
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }

        // Pas d'espaces
        if (preg_match('/\s/', $password)) {
            $errors[] = "Le mot de passe ne doit pas contenir d'espaces";
        }

        return $errors;
    }

    public static function sanitizeSearchTerm(?string $term): string
    {
        if (empty($term)) {
            return '';
        }
        
        $cleaned = strip_tags($term);
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
        return trim($cleaned);
    }

}
