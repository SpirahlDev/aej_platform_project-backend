# QueryParamsHandler - Documentation

## Vue d'ensemble

`QueryParamsHandler` est une classe utilitaire Laravel qui facilite la création d'API REST avec filtrage, recherche, tri et pagination automatiques basés sur les paramètres de requête HTTP.

## Installation

Placez la classe dans votre dossier `app/Helpers`.

```php
<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

// Classe QueryParamsHandler...
```

## Exemple d'utilisation simple

```php
use App\Helpers\QueryParamsHandler;

public function index(Request $request)
{
    // Définir les champs autorisés
    $allowedFields = ['id', 'title', 'price', 'created_at'];
    
    // Créer une requête de base
    $query = Product::query();
    
    // Appliquer les filtres et récupérer les résultats paginés
    $results = (new QueryParamsHandler($query, $request, $allowedFields))
        ->handle()
        ->paginate();
    
    return response()->json($results);
}
```

## Configuration avancée

```php
public function index(Request $request)
{
    $query = User::query();
    
    $results = (new QueryParamsHandler(
        $query, 
        $request, 
        ['id', 'name', 'email', 'role', 'created_at'],  // Champs à retourner
        ['role', 'status', 'created_at'],               // Filtres autorisés
        ['name', 'email']                               // Champs de recherche
    ))
        ->withDateField('registration_date')
        ->withDefaultSort('name', 'asc')
        ->withPaginationLimits(20, 100)
        ->handle()
        ->paginate();
    
    return response()->json($results);
}
```

## Paramètres de requête supportés

### Recherche
```
GET /api/users?search=john
```

### Filtrage simple
```
GET /api/users?filter[role]=admin&filter[status]=active
```

### Filtrage avancé avec opérateurs
```
GET /api/users?filter[price][operator]=gt&filter[price][value]=100
```

Opérateurs disponibles:
- `eq` - égal à (=)
- `ne` - différent de (!=)
- `gt` - supérieur à (>)
- `gte` - supérieur ou égal à (>=)
- `lt` - inférieur à (<)
- `lte` - inférieur ou égal à (<=)
- `like` - contient (LIKE %value%)
- `in` - dans une liste (IN)
- `notin` - pas dans une liste (NOT IN)
- `isnull` - est null (IS NULL)
- `isnotnull` - n'est pas null (IS NOT NULL)

### Filtrage par date
```
GET /api/users?from=2023-01-01&to=2023-12-31
GET /api/users?from=2023-01-01&to=2023-12-31&dateField=updated_at
```

### Tri
```
GET /api/users?sort=created_at&order=desc
```

### Pagination
```
GET /api/users?page=2&limit=20
GET /api/users?all  // Tous les résultats sans pagination
```

## Méthodes de configuration

| Méthode | Description |
|---------|-------------|
| `withSearchableFields(array $fields)` | Définit les champs sur lesquels effectuer la recherche |
| `withDateField(string $field)` | Définit le champ de date pour le filtrage par plage |
| `withDefaultSort(string $field, string $order)` | Configure le tri par défaut |
| `withPaginationLimits(int $default, int $max)` | Configure les limites de pagination |

## Méthodes principales

| Méthode | Retour | Description |
|---------|--------|-------------|
| `handle()` | `$this` | Applique tous les filtres, recherches et tris |
| `paginate()` | Collection/Paginator | Retourne les résultats paginés ou tous |
| `count()` | int | Retourne le nombre total de résultats |

## Constructeur

```php
/**
 * @param Builder $query          La requête Eloquent de base
 * @param Request $request        La requête HTTP
 * @param array $allowedFields    Les champs à retourner
 * @param array|null $allowedFilters  Les champs autorisés pour le filtrage
 * @param array|null $searchableFields Les champs de recherche
 */
public function __construct(
    Builder $query, 
    Request $request, 
    array $allowedFields, 
    ?array $allowedFilters = null,
    ?array $searchableFields = null
)
```

## Notes de sécurité

- Les champs utilisés pour le tri et le filtrage sont validés par rapport aux listes autorisées
- La classe utilise les fonctionnalités de Laravel pour éviter les injections SQL
- Les paramètres sont nettoyés et validés avant utilisation

## Implémentation complète

```php
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
```
