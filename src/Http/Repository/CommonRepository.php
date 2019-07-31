<?php

namespace Ifantace\Common\Http\Repositories;

use Schema;

class CommonRepository
{
    protected $query;
    protected $database_name;
    protected $table_name;

    public function __construct($query, $database_name, $table_name)
    {
        $this->query = $query;
        $this->database_name = $database_name;
        $this->table_name = $table_name;
    }

    public function first()
    {
        return $this->query->first();
    }
    public function get()
    {
        return $this->query->get();
    }
    public function delete()
    {
        return $this->query->delete();
    }
    public function count()
    {
        return $this->query->count();
    }
    public function update(array $update_data)
    {
        return $this->query->update($update_data);
    }
    public function create(array $create_data)
    {
        return $this->query->create($create_data);
    }
    public function firstOrCreate(array $first_data, array $create_data)
    {
        return $this->query->firstOrCreate($first_data, $create_data);
    }
    public function searchAllColumn(array $parameter, array  $columns_not_search = array(), array  $columns_change_search = array())
    {
        if (isset($parameter['query'])) {
            $query_string = $parameter['query'];
            $columns = Schema::connection($this->database_name)->getColumnListing($this->table_name);
            $columns_not_search = array_merge($columns_not_search, array_keys($columns_change_search));
            $columns = array_values(array_diff($columns, $columns_not_search));
            if (preg_match('/[^A-Za-z0-9: ]/', $query_string)) {
                $columns = array_values(array_diff($columns, ["created_at", "updated_at", "deleted_at"]));
            }
            $this->query->where(function ($query_all_column) use ($columns, $query_string, $columns_change_search) {
                foreach ($columns as $column) {
                    $query_all_column->orWhere($column, 'like', '%' . $query_string . '%');
                }
                foreach ($columns_change_search as $search_column_name => $change_key_array) {
                    foreach ($change_key_array as $inside_value => $outter_value) {
                        if (strpos($outter_value, $query_string) !== false) {
                            $query_all_column->orWhere($search_column_name, 'like', '%' . $inside_value . '%');
                        }
                    }
                }
            });
        }
        return $this;
    }
    public function getTable(array $parameter)
    {
        $count = $this->query->count();
        if (isset($parameter['orderBy']) && isset($parameter['ascending'])) {
            $orderBy = $parameter['orderBy'];
            $ascending = $parameter['ascending'];
            $this->query->orderBy($orderBy, $ascending == 1 ? "ASC" : "DESC");
        } else {
            $this->query->orderBy('created_at', "DESC");
        }
        if (isset($parameter['page']) && isset($parameter['limit'])) {
            $page = $parameter['page'];
            $limit = $parameter['limit'];
            $this->query->skip(($page - 1) * $limit);
        }
        if (isset($parameter['limit'])) {
            $limit = $parameter['limit'];
            $this->query->take($limit);
        }
        if (isset($parameter['select'])) {
            $this->query->select($parameter['select']);
        }
        if (isset($parameter["with"])) {
            $this->query->with($parameter['with']);
        }
        if (isset($parameter["with_count"])) {
            $this->query->withCount($parameter['with_count']);
        }
        $data = $this->query->get();
        return ['count' => $count, 'data' => $data];
    }
}
