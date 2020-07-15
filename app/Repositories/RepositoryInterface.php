<?php


namespace App\Repositories;


interface RepositoryInterface
{
    /**
     * @return mixed
     */
    public function all();

    /**
     * @param $id
     * @param array $relations
     * @return mixed
     */
    public function find($id , array $relations = []);

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data);

    /**
     * @param $field
     * @param $value
     * @param array $relations
     * @return mixed
     */
    public function findBy($field, $value, array $relations = []);

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);
}