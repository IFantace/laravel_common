<?php

namespace Ifantace\Common\Http\Repositories;

use Illuminate\Support\Facades\Schema;

class CommonRepository
{
    protected $model;
    protected $connection_name;
    protected $table_name;
    protected $columns;

    public function __construct($model)
    {
        $this->model = $model;
        $this->connection_name = $this->model->getConnectionName();
        $this->table_name = $this->model->getTable();
        $this->columns = Schema::connection($this->connection_name)->getColumnListing($this->table_name);
    }
    public function first()
    {
        return $this->model->first();
    }
    public function get()
    {
        return $this->model->get();
    }
    public function select(array $columns)
    {
        $this->model = $this->model->select($columns);
        return $this;
    }
    public function pluck($column)
    {
        return $this->model->pluck($column);
    }
    public function delete()
    {
        return $this->model->delete();
    }
    public function count()
    {
        return $this->model->count();
    }
    public function update(array $update_data)
    {
        return $this->model->update($update_data);
    }
    public function saveLikeUpdate(array $update_data)
    {
        $data = $this->model->first();
        if ($data === null) {
            return false;
        }
        foreach ($update_data as $each_key => $each_value) {
            $data->$each_key = $each_value;
        }
        return $data->save();
    }
    public function create(array $create_data)
    {
        return $this->model->create($create_data);
    }
    public function saveLikeCreate(array $create_data)
    {
        foreach ($create_data as $each_key => $each_value) {
            $this->model->$each_key = $each_value;
        }
        return $this->model->save();
    }
    public function firstOrCreate(array $first_data, array $create_data)
    {
        return $this->model->firstOrCreate($first_data, $create_data);
    }
    public function updateOrCreate(array $query_condition, array $data_array)
    {
        return $this->model->updateOrCreate($query_condition, $data_array);
    }
    public function searchAllColumn(
        array $parameter,
        array $columns_not_search = array(),
        array $columns_change_search = array(),
        array $special_column = array()
    ) {
        if (isset($parameter['query'])) {
            $query_string = $parameter['query'];
            $columns_not_search = array_merge($columns_not_search, array_keys($columns_change_search));
            $columns = array_values(array_diff($this->columns, $columns_not_search));
            if (preg_match('/[^A-Za-z0-9: ]/', $query_string)) {
                $columns = array_values(array_diff($columns, ["created_at", "updated_at", "deleted_at"]));
            }
            $this->model = $this->model->where(
                function ($query_all_column) use ($columns, $query_string, $columns_change_search, $special_column) {
                    foreach ($columns as $each_column) {
                        $query_all_column->orWhere($each_column, 'like', '%' . $query_string . '%');
                    }
                    foreach ($special_column as $column_name => $value_array) {
                        $query_all_column->orWhereIn($column_name, $value_array);
                    }
                    foreach ($columns_change_search as $search_column_name => $change_key_array) {
                        foreach ($change_key_array as $inside_value => $outer_value) {
                            if (strpos($outer_value, $query_string) !== false) {
                                $query_all_column->orWhere($search_column_name, 'like', '%' . $inside_value . '%');
                            }
                        }
                    }
                }
            );
        }
        return $this;
    }
    public function getTable(array $parameter)
    {
        $count = $this->model->count();
        if (isset($parameter['orderBy']) && isset($parameter['ascending'])) {
            $orderBy = $parameter['orderBy'];
            $ascending = $parameter['ascending'];
            $this->model = $this->model->orderBy($orderBy, $ascending == 1 ? "ASC" : "DESC");
        } else {
            $this->model = $this->model->orderBy('created_at', "DESC");
        }
        if (isset($parameter['page']) && isset($parameter['limit'])) {
            $page = $parameter['page'];
            $limit = $parameter['limit'];
            $this->model = $this->model->skip(($page - 1) * $limit);
        }
        if (isset($parameter['limit'])) {
            $limit = $parameter['limit'];
            $this->model = $this->model->take($limit);
        }
        if (isset($parameter['select'])) {
            $this->model = $this->model->select($parameter['select']);
        }
        if (isset($parameter["with"])) {
            $this->model =  $this->model->with($parameter['with']);
        }
        if (isset($parameter["with_count"])) {
            $this->model = $this->model->withCount($parameter['with_count']);
        }
        $data = $this->model->get();
        return ['count' => $count, 'data' => $data];
    }
}
