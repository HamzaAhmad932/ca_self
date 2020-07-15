<?php


namespace App\Repositories;

class BaseRepository implements RepositoryInterface
{
    /**
     * Usage Example given Below
     * app()->makeWith(ModelName::class,
     * ['tenantKey' => 'user_account_id', 'tenantValue' => auth()->user()->user_account_id])->all();
     */

    protected $modelName;
    protected $tenantKey;
    protected $tenantValue;

    /**
     * @return mixed
     */
    public function all( array $relations = [])
    {
        return $this->getNewInstance()->with($relations)->get();
    }


    /**
     * @param $id
     * @param array $relations
     * @return mixed
     */
    public function find($id, array $relations = [])
    {
        return $this->getNewInstance()->where('id', $id)->with($relations)->first();
    }

    /**
     * @param $field
     * @param $value
     * @param array $relations
     * @return mixed
     */
    public function findBy($field, $value, array $relations = [])
    {
       return $this->getNewInstance()::where($field, $value)->with($relations)->first();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data)
    {
       return $this->getNewInstanceWithOutTenantCheck()::create($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        $instance = $this->getNewInstanceWithOutTenantCheck();
        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }
        $instance->save();
        return $instance;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->find($id)->delete();
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return $this->find($id)->update($data);
    }

    /**
     * @param mixed $modelName
     */
    protected function setModelName($modelName): void
    {
        $this->modelName = $modelName;
    }

    /**
     * @return mixed
     */
    protected function getNewInstance()
    {
        if (!is_null($this->tenantKey))
            return $this->getNewInstanceWithOutTenantCheck()::where($this->tenantKey, $this->tenantValue);
        else
            return $this->getNewInstanceWithOutTenantCheck();
    }

    /**
     * @return mixed
     */
    private function getNewInstanceWithOutTenantCheck()
    {
        return resolve($this->modelName);
    }
}