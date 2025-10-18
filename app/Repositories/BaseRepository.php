<?php
namespace App\Repositories;

use \Illuminate\Pagination\Paginator;

class BaseRepository
{
    protected $model;

    public function __construct($model = null)
    {
        if ($model) {
            $this->model = $model;
        }
    }

    /**
    initRow
     */
    public function initRow(): array
    {
        return $this->getFillableColumns(
            $this->model::FILLABLE,
            $this->model::ATTRIBUTES
        );
    }

    private function getFillableColumns(array $fillable, array $attributes = [])
    {
        $columns = [];
        foreach ($fillable as $key) {
            if (isset($attributes[$key])) {
                $columns[$key] = $attributes[$key];
            } elseif (is_null($attributes[$key])) {
                $columns[$key] = null;
            } else {
                $columns[$key] = '';
            }
        }
        return $columns;
    }

    private function arrangeParamsCast(array $input, array $editable)
    {
        $params = [];

        foreach ($input as $key => $value) {
            if (in_array($key, $editable)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findWithTrashed($id, $columns = ['*'])
    {
        return $this->model->withTrashed()->find($id, $columns);
    }

    /**
     * @param string $key
     * @param string $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findByKey($key, $value, $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where($key, $value)
            ->first();
    }

    /**
     * @param array $wheres
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findByWhere($wheres, $columns = ['*'])
    {
        return $this->model->select($columns)
            ->where($wheres)
            ->first();
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data, array $options = [])
    {
        // 在 API 環境中，使用 JWT 認證取得使用者資訊
        try {
            if (auth('api')->check()) {
                $user = auth('api')->user();
                // 注入 company_id（若模型支援）
                $fillable = method_exists($this->model, 'getFillable') ? $this->model->getFillable() : [];
                if (in_array('company_id', $fillable) && isset($user->company_id)) {
                    $data['company_id'] = $user->company_id;
                }
            }
        } catch (\Exception $e) {
        }

        $editable = $this->model::FILLABLE;
        $data     = $this->arrangeParamsCast($data, $editable);
        return $this->model->create($data);
    }

    /**
     * @param $id
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update($id, array $params, array $options = [])
    {
        $editable   = $this->model::FILLABLE;
        $attributes = $this->arrangeParamsCast($params, $editable);
        $instance   = $this->model->findOrFail($id);
        return $instance->update($attributes, $options);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array $options
     * @return object
     */
    public function updateOrCreate(array $params, array $where, array $options = [])
    {
        $editable   = $this->model::FILLABLE;
        $attributes = $this->arrangeParamsCast($params, $editable);
        return $this->model->updateOrCreate($where, $attributes);
    }

    /**
     * @param array $wheres
     * @param array $params
     * @return bool
     */
    public function updateByWhere($wheres, $params)
    {
        return $this->model->where($wheres)
            ->update($params);
    }

    /**
     * @param array $wheres
     * @param array $params
     * @return bool
     */
    public function updateByWhereArray($wheres, $params)
    {
        $query = $this->model;
        foreach ($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }
        $query->update($params);
        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id, $force = false)
    {
        if ($force) {
            return $this->model->where('id', $id)->forceDelete();
        }
        return $this->model->where('id', $id)->delete();
    }

    /**
     * @param array $where
     * @return bool
     */
    public function deleteByWheres($wheres, $force = false)
    {
        if ($force) {
            return $this->model->where($wheres)->forceDelete();
        }
        return $this->model->where($wheres)->delete();
    }

    /**
     * @param array $where
     * @return bool
     */
    public function deleteByWhereArray($wheres, $force = false)
    {
        $query = $this->model;
        foreach ($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        if ($force) {
            return $query->forceDelete();
        }
        return $query->delete();
    }

    /**
     * @param array $wheres
     * @param array $columns
     * @return object
     */
    public function getByWheres($wheres = [], $columns = ['*'], $orderBy = [])
    {
        $query = $this->model->select($columns);
        $query->where($wheres);
        if (! empty($orderBy)) {
            foreach ($orderBy as $key => $value) {
                $query->orderBy($key, $value);
            }
        }
        return $query->get();
    }

    /**
     * @param array $where
     * @param array $columns
     * @return object
     */
    public function getByWhereArray($wheres = [], $columns = ['*'])
    {
        $query = $this->model->select($columns);
        foreach ($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }
        return $query->get();
    }

    /**
     * @param array $columns
     * @param array $input
     * @return object
     */
    public function queryAll($columns = ['*'], $input = null)
    {
        $query = $this->model->select($columns);

        if (! empty($input['wheres']) && is_array($input['wheres'])) {
            foreach ($input['wheres'] as $key => $value) {
                $query->where($key, $value);
            }
        }
        if (! empty($input['whereIn']) && is_array($input['whereIn'])) {
            foreach ($input['whereIn'] as $key => $value) {
                $query->whereIn($key, $value);
            }
        }
        if (! empty($input['limit'])) {
            $query->limit($input['limit']);
        }
        return $query->get();
    }

    /**
     * @param array $input
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getPaginatorList(array $input = [])
    {
        $page_size    = (! empty($input['page_size'])) ? $input['page_size'] : 99;
        $current_page = (! empty($input['page'])) ? $input['page'] : 1;

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $select = $input['select'] ?? ['*'];

        $query = $this->model->select($select);

        // eager load relations if provided
        if (! empty($input['with'])) {
            $query->with($input['with']);
        }

        if (! empty($input['joins'])) {
            foreach ($input['joins'] as $key => $join) {
                // left or inner
                $type = $join[4] ?? 'inner';
                if (! empty($join[5])) {
                    // conditions
                    $query->join($join[0], function ($obj) use ($join) {
                        $input = $join[5];
                        $obj->on($join[1], $join[2], $join[3]);
                    }, null, null, $type);
                } else {
                    $query->join($join[0], $join[1], $join[2], $join[3], $type);
                }
            }
        }

        //where
        if (! empty($input['wheres']) && is_array($input['wheres'])) {
            foreach ($input['wheres'] as $where) {
                if (is_array($where) && count($where) >= 3) {
                    // 支援 [column, operator, value] 格式
                    $query->where($where[0], $where[1], $where[2]);
                } elseif (is_array($where) && count($where) == 2) {
                    // 支援 [column, value] 格式，預設使用 = 操作符
                    $query->where($where[0], '=', $where[1]);
                }
            }
        }

        //wherein
        if (! empty($input['whereIn']) && is_array($input['whereIn'])) {
            foreach ($input['whereIn'] as $key => $array) {
                $query->whereIn($key, $array);
            }
        }

        //orWhere
        if (! empty($input['orWheres']) && is_array($input['orWheres'])) {
            $orWheres = $input['orWheres'];
            $query->where(function ($query) use ($orWheres) {
                foreach ($orWheres as $wheres) {
                    foreach ($wheres as $key => $value) {
                        $query->orWhere($key, 'like', "%$value%");
                    }
                }
            });
        }

        //orderBy
        if (! empty($input['sort_by'])) {

            $input['sort_by'] = $this->model->getTable() . '.' . $input['sort_by'];

            $input['sort_rule'] = (empty($input['sort_rule'])) ? 'asc' : $input['sort_rule'];
            $query->orderBy($input['sort_by'], $input['sort_rule']);
        }

        return $query->paginate($page_size);
    }

    /**
     * getMaxSortNum
     *
     * @param  mixed $wheres
     * @return int
     */
    public function getMaxSortNum($key, $wheres)
    {
        return $this->model->where($wheres)->max($key);
    }

    /**
    all
     */
    public function all(array $params = []): object
    {
        return $this->commonQuery($params)->get();
    }

    /**
    allBy
     */
    public function allBy(int $id, string $key, array $columns = ['*']): object
    {
        return $this->model
            ->select($columns)
            ->where($key, $id)
            ->get();
    }

    private function commonQuery(array $params): object
    {
        $selects  = $params['selects'] ?? '*';
        $distinct = $params['distinct'] ?? false;

        $query = $this->model->select($selects);
        if ($distinct) {
            $query->distinct();
        }

        //where
        if (! empty($params['wheres']) && is_array($params['wheres'])) {
            $query->where($params['wheres']);
        }

        //wheresIn
        if (! empty($params['wheresIn']) && is_array($params['wheresIn'])) {
            foreach ($params['wheresIn'] as $key => $array) {
                $query->whereIn($key, $array);
            }

        }

        //orWhere
        if (! empty($params['orWheres']) && is_array($params['orWheres'])) {
            $orWheres = $params['orWheres'];
            $query->where(function ($query) use ($orWheres) {
                foreach ($orWheres as $index => $wheres) {
                    foreach ($wheres as $key => $value) {
                        $query->orWhere($key, 'like', "%$value%");
                    }
                }
            });
        }

        //orderBy
        if (! empty($params['sort_by'])) {
            if (is_array($params['sort_by'])) {
                foreach ($params['sort_by'] as $key => $sortBy) {
                    $query->orderBy($sortBy, $params['sort_rule'][$key] ?? 'ASC');
                }
            } else {
                $params['sort_rule'] = (empty($params['sort_rule'])) ? 'ASC' : $params['sort_rule'];
                $query->orderBy($params['sort_by'], $params['sort_rule']);
            }
        }

        return $query;
    }

}
