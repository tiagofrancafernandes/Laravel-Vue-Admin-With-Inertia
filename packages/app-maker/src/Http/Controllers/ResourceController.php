<?php

namespace AppMaker\Http\Controllers;

use AppMaker\Resources\ResourceBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController
{
    public function index(Request $request, string $resource): Response
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('view')) {
            abort(403, 'Unauthorized');
        }

        $table = $resourceInstance->table();
        $query = $this->buildQuery($resourceInstance, $request, $table);

        $perPage = $request->get('per_page', $table->getPaginationConfig()['default']);
        $records = $query->paginate($perPage)->appends($request->query());

        return Inertia::render('AppMaker/ResourceListPage', [
            'resource' => $resource,
            'resourceConfig' => [
                'uri' => $resourceInstance->getUri(),
                'title' => $resourceInstance->getTitle(),
                'singularLabel' => $resourceInstance->getSingularLabel(),
                'pluralLabel' => $resourceInstance->getPluralLabel(),
            ],
            'table' => $table->toArray(),
            'records' => $records,
        ]);
    }

    public function create(Request $request, string $resource): Response
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('create')) {
            abort(403, 'Unauthorized');
        }

        $form = $resourceInstance->form();

        return Inertia::render('AppMaker/ResourceCreatePage', [
            'resource' => $resource,
            'resourceConfig' => [
                'uri' => $resourceInstance->getUri(),
                'singularLabel' => $resourceInstance->getSingularLabel(),
            ],
            'form' => $form->toArray(),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('create')) {
            abort(403, 'Unauthorized');
        }

        $form = $resourceInstance->form();
        $validated = $request->validate($form->getValidationRules());

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::create($validated);

        return redirect()
            ->route("{$resourceInstance->getUri()}.index")
            ->with('success', "{$resourceInstance->getSingularLabel()} created successfully.");
    }

    public function show(Request $request, string $resource, $id): Response
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('view')) {
            abort(403, 'Unauthorized');
        }

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::findOrFail($id);

        $infoList = $resourceInstance->infoList();

        return Inertia::render('AppMaker/ResourceShowPage', [
            'resource' => $resource,
            'resourceConfig' => [
                'uri' => $resourceInstance->getUri(),
                'singularLabel' => $resourceInstance->getSingularLabel(),
            ],
            'infoList' => $infoList ? $infoList->toArray() : null,
            'record' => $record,
        ]);
    }

    public function edit(Request $request, string $resource, $id): Response
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('update')) {
            abort(403, 'Unauthorized');
        }

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::findOrFail($id);

        $form = $resourceInstance->form();

        return Inertia::render('AppMaker/ResourceEditPage', [
            'resource' => $resource,
            'resourceConfig' => [
                'uri' => $resourceInstance->getUri(),
                'singularLabel' => $resourceInstance->getSingularLabel(),
            ],
            'form' => $form->toArray(),
            'record' => $record,
        ]);
    }

    public function update(Request $request, string $resource, $id): RedirectResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('update')) {
            abort(403, 'Unauthorized');
        }

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::findOrFail($id);

        $form = $resourceInstance->form();
        $validated = $request->validate($form->getValidationRules());

        $record->update($validated);

        return redirect()
            ->route("{$resourceInstance->getUri()}.index")
            ->with('success', "{$resourceInstance->getSingularLabel()} updated successfully.");
    }

    public function destroy(Request $request, string $resource, $id): RedirectResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        if (!$resourceInstance->authorize('delete')) {
            abort(403, 'Unauthorized');
        }

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::findOrFail($id);

        $record->delete();

        return redirect()
            ->route("{$resourceInstance->getUri()}.index")
            ->with('success', "{$resourceInstance->getSingularLabel()} deleted successfully.");
    }

    protected function resolveResource(string $resource): ResourceBase
    {
        $class = config("appmaker.resources.{$resource}");

        if (!$class || !class_exists($class)) {
            abort(404, "Resource {$resource} not found");
        }

        return new $class();
    }

    protected function buildQuery(ResourceBase $resource, Request $request, $table): Builder
    {
        $modelClass = $resource->getModel();
        $query = $modelClass::query();

        // Apply search
        if ($search = $request->get('search')) {
            $searchConfig = $table->getSearchConfig();
            $this->applySearch($query, $search, $searchConfig);
        }

        // Apply filters
        if ($filters = $request->get('filters')) {
            foreach ($table->getFilters() as $filterConfig) {
                if (isset($filters[$filterConfig['name']])) {
                    $filter = $table->getFilterByName($filterConfig['name']);

                    if ($filter) {
                        $filter->apply($query, $filters[$filterConfig['name']]);
                    }
                }
            }
        }

        // Apply sorting
        $sortingConfig = $table->getSortingConfig();
        $sortBy = $request->get('sort_by', $sortingConfig['default_column']);
        $sortDirection = $request->get('sort_direction', $sortingConfig['default_direction']);

        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Auto eager load relationships
        $relationships = $this->detectRelationships($table->getColumns());

        if (!empty($relationships)) {
            $query->with($relationships);
        }

        return $query;
    }

    protected function applySearch(Builder $query, string $search, array $searchConfig): void
    {
        if (!$searchConfig['enabled']) {
            return;
        }

        $query->where(function ($q) use ($search, $searchConfig) {
            foreach ($searchConfig['columns'] as $column) {
                if (str_contains($column, '.')) {
                    // Relationship search
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn ($query) => $query->where($field, 'like', "%{$search}%"));
                } else {
                    // Direct column search
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    protected function detectRelationships(array $columns): array
    {
        $relationships = [];

        foreach ($columns as $column) {
            if (str_contains($column['name'], '.')) {
                [$relation] = explode('.', $column['name'], 2);
                $relationships[] = $relation;
            }
        }

        return array_unique($relationships);
    }
}
