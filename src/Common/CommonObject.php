<?php
/*
 * @Author: Austin
 * @Date: 2019-12-27 17:49:13
 * @LastEditors  : Austin
 * @LastEditTime : 2020-01-13 19:08:20
 */

namespace App\Manager;

use Ifantace\Common\CommonTraits;
use Illuminate\Support\Facades\Auth;

abstract class CommonObject
{
    use CommonTraits;
    protected $repository;
    protected $creator;
    /**
     * 所有欄位
     */
    protected $all_column;
    /**
     * 可新增的欄位
     */
    protected $fillable_column;
    /**
     * 可人為設定的欄位
     */
    protected $settable_column;
    /**
     * 可被更新的欄位
     */
    protected $updatable_column;
    /**
     * 初始化，如果有primary key，則搜尋並設定data
     *
     * @param [type] $primary_key
     */
    public function __construct($primary_key = null)
    {
        $this->initColumn();
        $this->setCreator();
        if ($primary_key !== null) {
            $this->setDataByPrimary($primary_key);
        }
    }
    public function setCreator()
    {
        $this->creator = Auth::user();
        $this->creator_uuid = $this->creator->uuid;
    }
    /**
     * 透過primary key，則搜尋並設定data
     *
     * @param [type] $primary_key
     * @return void
     */
    public function setDataByPrimary($primary_key)
    {
        $data = $this->findDataByPrimary($primary_key);
        if ($data !== null) {
            $data = $data->toArray();
            $this->setData($data, $this->all_column);
        }
    }
    /**
     * 設定data
     *
     * @param [type] $data
     * @return void
     */
    public function setData($data, $column = null)
    {
        $this_column = $column === null ? $this->settable_column : $column;
        foreach ($this_column as $each_column) {
            if (isset($data[$each_column])) {
                $this->$each_column = $data[$each_column];
            }
        }
        $this->initSystemData();
        return $this;
    }
    /**
     * 透過指定的欄位產生array
     *
     * @param [type] $columns
     * @return array
     */
    protected function filterByColumn($columns)
    {
        $return_array = [];
        foreach ($columns as $each_column) {
            if (isset($this->$each_column)) {
                $return_array[$each_column] = $this->$each_column;
            }
        }
        return $return_array;
    }
    /**
     * 透過可全部的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByAllColumn()
    {
        return $this->filterByColumn($this->all_column);
    }
    /**
     * 透過可新增的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByFillableColumn()
    {
        return $this->filterByColumn($this->fillable_column);
    }
    /**
     * 透過可設定的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnBySettableColumn()
    {
        return $this->filterByColumn($this->settable_column);
    }
    /**
     * 透過設定的可更新的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByUpdatableColumn()
    {
        return $this->filterByColumn($this->updatable_column);
    }
    /**
     * 初始化Object可用欄位
     *
     * @return void
     */
    abstract public function initColumn();
    /**
     * 初始化由系統指定的參數
     *
     * @return void
     */
    abstract public function initSystemData();
    /**
     * 建立資料
     *
     * @return void
     */
    abstract public function create();
    /**
     * 更新資料
     *
     * @return void
     */
    abstract public function update();
    /**
     * 刪除資料
     *
     * @return void
     */
    abstract public function delete();
    /**
     * 搜尋重複unique欄位的資料
     *
     * @return void
     */
    abstract public function findDuplicate();
    /**
     * 搜尋指定的Primary的資料
     *
     * @return void
     */
    abstract public function findDataByPrimary($primary_key);
}
