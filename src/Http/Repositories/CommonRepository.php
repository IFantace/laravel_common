<?php

namespace Ifantace\Common\Http\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class CommonRepository
{
    /**
     * this repository model
     *
     * @var Model
     */
    protected $model;

    /**
     * model connection
     *
     * @var string
     */
    protected $connection_name;

    /**
     * model table
     *
     * @var string
     */
    protected $table_name;

    /**
     * table columns
     *
     * @var array
     */
    protected $columns;

    /**
     * construct
     *
     * @param Model $model model
     */
    public function __construct(Model $model)
    {
        $this->setModel($model);
    }

    /**
     * init repository
     *
     * @return void
     */
    protected function init()
    {
        $this->connection_name = $this->model->getConnectionName();
        $this->table_name = $this->model->getTable();
        $this->columns = Schema::connection($this->connection_name)->getColumnListing($this->table_name);
    }

    /**
     * return current model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * set model
     *
     * @param Model $model model
     * @return static
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->init();
        return $this;
    }

    /**
     * run function by string
     *
     * @param string $function_name function name
     * @return mixed
     */
    public function modelRunFunction(string $function_name)
    {
        return $this->model->$function_name();
    }

    /**
     * search table
     *
     * @param string $query_string
     * @param array $columns_not_search just array eq.["A","B","C"]
     * @param array $columns_change_search two-dimensional array
     * eq. ["A"=> ["1" => "正常","0" => "關閉中","-1" => "申請中","-2" => "拒絕申請",]]
     * @param array $special_column two-dimensional array
     * eq. ["A"=> ["1", "0", "-1", "-2"]]
     * @return static
     */
    public function searchAllColumn(
        string $query_string,
        array $columns_not_search = array(),
        array $columns_change_search = array(),
        array $columns_whereIn = array()
    ) {
        $columns_not_search = array_merge($columns_not_search, array_keys($columns_change_search));
        $columns = array_values(array_diff($this->columns, $columns_not_search));
        $columns = array_values(array_diff($columns, ["created_at", "updated_at", "deleted_at"]));
        $this->model = $this->model->where(
            function ($query_all_column) use ($columns, $query_string, $columns_change_search, $columns_whereIn) {
                foreach ($columns as $each_column) {
                    $query_all_column->orWhere($each_column, 'like', '%' . $query_string . '%');
                }
                foreach ($columns_whereIn as $column_name => $value_array) {
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
        return $this;
    }

    /**
     * get data with table format
     *
     * @param array $table_config config of table
     * orderBy: "column name",
     * ascending: ["ASC","DESC"],
     * page: int => pagination,
     * limit: int => count of each pagination and this time take,
     * select: array => column need to select,
     * with: array => search relation,
     * with_count: array => count relation
     * @return array
     */
    public function getTable(array $table_config)
    {
        $count = $this->model->count();
        if (isset($table_config['orderBy']) && isset($table_config['ascending'])) {
            $orderBy = $table_config['orderBy'];
            $ascending = $table_config['ascending'];
            $this->model = $this->model->orderBy($orderBy, $ascending == 1 ? "ASC" : "DESC");
        } else {
            $this->model = $this->model->orderBy('created_at', "DESC");
        }
        if (isset($table_config['page']) && isset($table_config['limit'])) {
            $page = $table_config['page'];
            $limit = $table_config['limit'];
            $this->model = $this->model->skip(($page - 1) * $limit);
        }
        if (isset($table_config['limit'])) {
            $limit = $table_config['limit'];
            $this->model = $this->model->take($limit);
        }
        if (isset($table_config['select'])) {
            $this->model = $this->model->select($table_config['select']);
        }
        if (isset($table_config["with"])) {
            $this->model = $this->model->with($table_config['with']);
        }
        if (isset($table_config["with_count"])) {
            $this->model = $this->model->withCount($table_config['with_count']);
        }
        $data = $this->model->get();
        return ['count' => $count, 'data' => $data];
    }
}
