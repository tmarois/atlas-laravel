<?php

namespace Atlas\Laravel\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
        return ($this->model)::query();
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
        return $this->buildQuery($options)->get($columns);
    }

    /**
     * Retrieve a paginated list of models.
     *
     * @return LengthAwarePaginator<TModel>
     */
    public function listPaginated(int $perPage = 15, array $options = []): LengthAwarePaginator
    {
        return $this->buildQuery($options)
            ->when($options['sortField'] ?? false, function ($q) use ($options) {
                $direction = ($options['sortOrder'] ?? 1) === 1 ? 'asc' : 'desc';
                return $q->orderBy($options['sortField'], $direction);
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a model by primary key.
     *
     * @param mixed $id
     * @return TModel|null
     */
    public function find(mixed $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * Create a new model instance.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * Update the given model instance.
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    /**
     * Delete the given model instance.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
