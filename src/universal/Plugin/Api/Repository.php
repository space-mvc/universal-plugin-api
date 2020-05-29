<?php

namespace Universal\Plugin\Api;

use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


/**
 * Class Repository
 *
 * @package Universal\Plugin\Api
 */
class Repository extends BaseRepository
{
    /**
     * model.
     *
     * @return mixed
     */
    public function model() {
        return $this->model;
    }

    /**
     * getModel.
     *
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * setModel.
     *
     * @param mixed $model
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }
}
