<?php

namespace App\Repositories\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;

trait CommonCrudTrait
{
    /**
     * Auto-guess the name of the relevant model
     *
     * @return mixed
     */
    protected function getModelClass()
    {
        return str_replace('Repository', '', '\\App\\Models\\'.class_basename(__CLASS__));
    }

    /**
     * Find a model instance or fail.
     * If failure, query builder will throw \Illuminate\Database\Eloquent\ModelNotFoundException
     * and we'll just let that bubble up.
     *
     * @param int $id
     * @param array $eagerLoad
     * @param bool $trashed
     * @see http://laravel.com/docs/master/eloquent#soft-deleting
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, array $eagerLoad = [], $trashed = false)
    {
        $model = $this->getModelClass();
        $query = $model::with($eagerLoad);
        if ($trashed) {
            $query->withTrashed();
        }

        // if we pass in an array of ids, grab all the ids
        if (is_array($id)) {
            return $query->whereIn('id', $id)->get();
        }
        return $query->where('id', $id)->first();
        return $query->find($id);
    }

    /**
     * Same as find() but allow trashed item
     *
     * @param int $id
     * @param array $eagerLoad
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findTrashed($id, array $eagerLoad = [])
    {
        return $this->find($id, $eagerLoad, true);
    }

    /**
     * Get all the instances of the model
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $eagerLoad = [])
    {
        $model = $this->getModelClass();
        return $model::with($eagerLoad)->get();
    }

    /**
     * Get a paginated list of instances for a model
     *
     * @param int $perPage
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function paginate(int $perPage = 15)
    {
        $model = $this->getModelClass();
        return $model::paginate($perPage);
    }

    /**
     * Delete a specified model instance, or the instance indicated by its id
     *
     * @param \Illuminate\Database\Eloquent\Model|int $item
     * @return bool
     */
    public function delete($item)
    {
        $model = $this->getModelClass();
        if (! $item instanceof $model) {
            $item = $this->find($item);
        }

        if (! $item) {
            return false;
        }

        $deleted = $item->delete();
        return (bool) $deleted;
    }

    /**
     * Restore a specified model instance, or the instance indicated by its id
     *
     * @param \Illuminate\Database\Eloquent\Model|int $item
     * @return \Illuminate\Database\Eloquent\Model|bool
     */
    public function restore($item)
    {
        $model = $this->getModelClass();
        if (! $item instanceof $model) {
            $item = $this->find($item, [], true);
        }

        if (! $item) {
            return false;
        }

        $restored = $item->restore();
        if ($restored) {
            return $item;
        }
        return false;
    }

    /**
     * Convenience method to set a single field value on a DbModel (Eloquent) instance, save it, and raise
     * an 'updated' event.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param mixed $key
     * @param bool $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setFieldAndSave(Model $model, $key, $value = false)
    {
        if (! isset($model->$key)) {
            throw new \LogicException($key.' does not exist on '.class_basename($model));
        }

        //if no change, just return and move on.
        if ($model->$key === $value) {
            return $model;
        }

        $model->$key = $value;
        $model->save();
        return $model;
    }
}
