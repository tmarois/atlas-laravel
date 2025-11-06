<?php

namespace Atlas\Laravel\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;

/**
 * Base service for Eloquent models providing simple CRUD methods.
 *
 * Extend this service and set the model class on the consumer side:
 *
 * ```php
 * class UserService extends ModelService
 * {
 *     protected string $model = User::class;
 * }
 * ```
 */
/**
 * @template TModel of Model
 *
 * @psalm-consistent-constructor
 */
abstract class ModelService
{
    /**
     * The model class managed by the service.
     *
     * @var class-string<TModel>
     */
    protected string $model;

    /**
     * Get a new query builder for the model.
     *
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        return $this->resolveModelClass()::query();
    }

    /**
     * Build a base query for the model. Override to apply filters.
     *
     * @return Builder<TModel>
     */
    public function buildQuery(array $options = []): Builder
    {
        return $this->query();
    }

    /**
     * Retrieve all models.
     *
     * @return Collection<int, TModel>
     */
    public function list(array $columns = ['*'], array $options = []): Collection
    {
        return $this->applyQueryOptions($this->buildQuery($options), $options)->get($columns);
    }

    /**
     * Retrieve a paginated list of models.
     *
     * @return LengthAwarePaginator<TModel>
     */
    public function listPaginated(int $perPage = 15, array $options = []): LengthAwarePaginator
    {
        $query = $this->applyQueryOptions($this->buildQuery($options), $options)
            ->when($options['sortField'] ?? false, function ($q) use ($options) {
                $direction = ($options['sortOrder'] ?? 1) === 1 ? 'asc' : 'desc';

                return $q->orderBy($options['sortField'], $direction);
            });

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Find a model by primary key.
     *
     * @return TModel|null
     */
    public function find(mixed $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * Create a new model instance.
     *
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * Update a model identified by its primary key.
     *
     * @return TModel
     */
    public function updateByKey(mixed $id, array $data): Model
    {
        $model = $this->find($id);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($this->resolveModelClass(), [$id]);
        }

        return $this->update($model, $data);
    }

    /**
     * Update the given model instance.
     *
     * @param  TModel  $model
     * @return TModel
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    /**
     * Delete the given model instance.
     *
     * @param  TModel  $model
     */
    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Resolve the configured model class, guarding against misconfiguration.
     *
     * @return class-string<TModel>
     */
    protected function resolveModelClass(): string
    {
        if (! isset($this->model) || $this->model === '') {
            throw new LogicException(sprintf(
                'No model class configured on %s. Set the protected $model property to an Eloquent model class.',
                static::class,
            ));
        }

        if (! is_a($this->model, Model::class, true)) {
            throw new LogicException(sprintf(
                'The configured $model on %s must be a class-string of %s.',
                static::class,
                Model::class,
            ));
        }

        return $this->model;
    }

    /**
     * Apply shared query options across retrieval methods.
     *
     * Supported options:
     *  - query (callable): invoked with the builder instance for custom constraints.
     *  - with (array|string): relations to eager load.
     *  - withCount (array|string): relations to eager load counts for.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function applyQueryOptions(Builder $query, array $options): Builder
    {
        if (isset($options['query']) && is_callable($options['query'])) {
            $options['query']($query);
        }

        if (isset($options['with'])) {
            $query->with($options['with']);
        }

        if (isset($options['withCount'])) {
            $query->withCount($options['withCount']);
        }

        return $query;
    }
}
