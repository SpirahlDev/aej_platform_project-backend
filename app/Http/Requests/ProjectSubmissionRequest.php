<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Tout utilisateur peut soumettre un projet
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Informations personnelles
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'id_card_number' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',

            // Informations du projet
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:1000',
            'description' => 'nullable|string',
            'project_type_id' => 'required|exists:project_type,id',
            'legal_form_id' => 'required|exists:legal_forms,id',

            // Documents requis
            'id_card_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'identity_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'business_plan' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'last_name' => 'Nom',
            'first_name' => 'Prénom(s)',
            'birth_date' => 'Date de Naissance',
            'birth_place' => 'Lieu de Naissance',
            'id_card_number' => 'Numéro de CNI',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'address' => 'Adresse',
            'title' => 'Titre du projet',
            'summary' => 'Résumé du projet',
            'description' => 'Description du projet',
            'project_type_id' => 'Type de Projet',
            'legal_form_id' => 'Forme Juridique',
            'id_card_file' => 'CNI',
            'identity_document' => 'Pièce d\'identité',
            'business_plan' => 'Plan d\'Affaires',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            '*.required' => 'Le champ :attribute est obligatoire.',
            '*.max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            '*.email' => 'Le champ :attribute doit être une adresse email valide.',
            '*.date' => 'Le champ :attribute doit être une date valide.',
            '*.before' => 'Le champ :attribute doit être une date antérieure à aujourd\'hui.',
            '*.exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            '*.file' => 'Le champ :attribute doit être un fichier.',
            '*.mimes' => 'Le fichier :attribute doit être de type: :values.',
            '*.max' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('birth_date')) {
            $this->merge([
                'birth_date' => date('Y-m-d', strtotime($this->birth_date)),
            ]);
        }
    }
}
