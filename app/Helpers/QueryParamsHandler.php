<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Gestionnaire avancé de paramètres de requête pour Laravel/Eloquent
 * Facilite la création d'API REST avec filtrage, recherche, tri et pagination
 */
class QueryParamsHandler
{
    protected $query;
    protected $request;
    protected $defaultLimit = 10;
    protected $maxLimit = 100;
    protected $defaultSortField = 'created_at';
    protected $defaultSortOrder = 'desc';
    protected $searchableFields = ['title', 'description'];
    protected $dateField = 'created_at';

    /**
     * Initialise le gestionnaire de requête
     *
     * @param Builder $query La requête Eloquent de base
     * @param Request $request La requête HTTP
     * @param array $allowedFields Les champs à retourner dans les résultats
     * @param array $allowedFilters Les champs autorisés pour le filtrage (si vide, utilise $allowedFields)
     * @param array $searchableFields Les champs sur lesquels effectuer la recherche
     */
    public function __construct(
        Builder $query, 
        Request $request, 
        protected array $allowedFields, 
        protected ?array $allowedFilters = null,
        ?array $searchableFields = null
    ) {
        if (empty($this->allowedFields)) {
            throw new \InvalidArgumentException('Les champs de colonnes ne doivent pas être vides pour la pagination');
        }
        
        $this->query = $query;
        $this->request = $request;
        
        // Si aucun filtre spécifique n'est fourni, utiliser les champs autorisés
        if ($this->allowedFilters === null) {
            $this->allowedFilters = $this->allowedFields;
        }
        
        // Si des champs de recherche spécifiques sont fournis, les utiliser
        if ($searchableFields !== null) {
            $this->searchableFields = $searchableFields;
        }
    }

    /**
     * Configure les champs sur lesquels effectuer la recherche
     *
     * @param array $fields Les champs de recherche
     * @return $this
     */
    public function withSearchableFields(array $fields)
    {
        $this->searchableFields = $fields;
        return $this;
    }

    /**
     * Configure le champ de date pour le filtrage par plage
     *
     * @param string $field Le nom du champ de date
     * @return $this
     */
    public function withDateField(string $field)
    {
        $this->dateField = $field;
        return $this;
    }

    /**
     * Configure les valeurs par défaut pour le tri
     *
     * @param string $field Le champ de tri par défaut
     * @param string $order L'ordre de tri par défaut (asc|desc)
     * @return $this
     */
    public function withDefaultSort(string $field, string $order = 'desc')
    {
        $this->defaultSortField = $field;
        $this->defaultSortOrder = $order;
        return $this;
    }

    /**
     * Configure les limites de pagination
     *
     * @param int $defaultLimit La limite par défaut
     * @param int $maxLimit La limite maximale
     * @return $this
     */
    public function withPaginationLimits(int $defaultLimit, int $maxLimit)
    {
        $this->defaultLimit = $defaultLimit;
        $this->maxLimit = $maxLimit;
        return $this;
    }

    /**
     * Applique tous les paramètres de requête
     *
     * @return $this
     */
    public function handle()
    {
        $this->query
            ->when($this->request->filled('search'), function ($query) {
                $this->applySearch($query);
            })
            ->when($this->request->has('filter'), function ($query) {
                $this->applyFilters($query);
            })
            ->when($this->request->filled('from') || $this->request->filled('to'), function ($query) {
                $this->applyDateRange($query);
            })
            ->when(true, function ($query) { 
                // Toujours appliquer le tri (avec les valeurs par défaut si non spécifiées)
                $this->applySort($query);
            });

        return $this;
    }

    /**
     * Applique la recherche sur les champs configurés
     */
    protected function applySearch($query): void
    {
        $searchTerm = $this->request->input('search');

        $query->where(function ($query) use ($searchTerm) {
            foreach ($this->searchableFields as $index => $field) {
                if ($index === 0) {
                    $query->where($field, 'LIKE', "%{$searchTerm}%");
                } else {
                    $query->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Applique les filtres spécifiés dans la requête
     */
    protected function applyFilters($query): void
    {
        $filters = $this->request->input('filter', []);
        
        if (!is_array($filters)) {
            // Convertir en tableau si ce n'est pas déjà le cas
            $filters = json_decode($filters, true) ?? [];
        }

        foreach ($filters as $field => $value) {
            // Ignorer les valeurs nulles ou vides
            if (!in_array($field, $this->allowedFilters) || $value === null || $value === '') {
                continue;
            }

            // Gérer les opérateurs complexes
            if (is_array($value) && isset($value['operator'], $value['value'])) {
                $this->applyOperatorFilter($query, $field, $value['operator'], $value['value']);
            } 
            // Filtrage simple par égalité
            else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Applique un filtre avec un opérateur spécifié
     */
    protected function applyOperatorFilter($query, $field, $operator, $value): void
    {
        switch (strtolower($operator)) {
            case 'eq':
                $query->where($field, '=', $value);
                break;
            case 'ne':
                $query->where($field, '!=', $value);
                break;
            case 'gt':
                $query->where($field, '>', $value);
                break;
            case 'gte':
                $query->where($field, '>=', $value);
                break;
            case 'lt':
                $query->where($field, '<', $value);
                break;
            case 'lte':
                $query->where($field, '<=', $value);
                break;
            case 'like':
                $query->where($field, 'LIKE', "%{$value}%");
                break;
            case 'in':
                $query->whereIn($field, Arr::wrap($value));
                break;
            case 'notin':
                $query->whereNotIn($field, Arr::wrap($value));
                break;
            case 'isnull':
                $query->whereNull($field);
                break;
            case 'isnotnull':
                $query->whereNotNull($field);
                break;
            default:
                $query->where($field, '=', $value);
                break;
        }
    }

    /**
     * Applique un filtrage par plage de dates
     */
    protected function applyDateRange($query): void
    {
        $from = $this->request->input('from');
        $to = $this->request->input('to');
        $dateField = $this->request->input('dateField', $this->dateField);

        if ($from && Carbon::hasFormat($from, 'Y-m-d')) {
            $query->whereDate($dateField, '>=', $from);
        }

        if ($to && Carbon::hasFormat($to, 'Y-m-d')) {
            $query->whereDate($dateField, '<=', $to);
        }
    }

    /**
     * Applique le tri
     */
    protected function applySort($query): void
    {
        $sortField = $this->request->input('sort', $this->defaultSortField);
        $sortOrder = strtolower($this->request->input('order', $this->defaultSortOrder));
        
        // Vérifier si le champ de tri est autorisé
        if (in_array($sortField, $this->allowedFields)) {
            // Sécuriser l'ordre de tri
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
            $query->orderBy($sortField, $sortOrder);
        } else {
            // Si le champ n'est pas autorisé, utiliser le tri par défaut
            $query->orderBy($this->defaultSortField, $this->defaultSortOrder);
        }
    }

    /**
     * Exécute la pagination ou récupère tous les résultats
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function paginate()
    {
        // Si 'all' est présent dans la requête, retourner tous les résultats
        if ($this->request->has('all')) {
            return $this->query->get($this->allowedFields);
        }

        $limit = min(
            (int) $this->request->input('limit', $this->defaultLimit),
            $this->maxLimit
        );
        $page = max(1, (int) $this->request->input('page', 1));

        return $this->query->paginate($limit, $this->allowedFields, 'page', $page);
    }

    /**
     * Récupère uniquement le nombre total de résultats
     *
     * @return int
     */
    public function count()
    {
        return $this->query->count();
    }
}