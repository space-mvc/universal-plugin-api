<?php

namespace Universal\Plugin\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class Controller
 *
 * @package Universal\Plugin\Api
 */
class Controller
{
    /** @var string $modelName */
    protected $modelName = 'App\Models\User';

    /** @var Headers $headers */
    protected $headers;

    /** @var Authenticate $authenticate */
    protected $authenticate;

    /** @var Parameter $parameter */
    protected $parameter;

    /** @var Repository $repository */
    protected $repository;

    /** @var Response $response */
    protected $response;

    /**
     * Controller constructor.
     *
     * @param Headers $headers
     * @param Authenticate $authenticate
     * @param Parameter $parameter
     * @param Repository $repository
     * @param Response $response
     */
    public function __construct(Headers $headers, Authenticate $authenticate, Parameter $parameter, Repository $repository, Response $response)
    {
        $this->headers = $headers;
        $this->authenticate = $authenticate;
        $this->parameter = $parameter;
        $this->repository = $repository;
        $this->repository->setModel(new $this->modelName());
        $this->response = $response;
    }

    /**
     * getEntities
     */
    public function getEntities()
    {
        try {

            // init query
            $query = $this->repository;

            // init fields
            $query = $query->select($this->parameter->getFields());

            // init with
            $query = $query->with($this->parameter->getWith());

            // init wheres
            $wheres = $this->parameter->getWheres();
            if(!empty($wheres)) {
                foreach($wheres as $where) {
                    $query = $query->where($where['key'], $where['operator'], $where['value']);
                }
            }

            // init orders
            $orders = $this->parameter->getOrders();
            if(!empty($orders)) {
                foreach($orders as $field => $direction) {
                    $query = $query->orderBy($field, $direction);
                }
            }

            $query = $query
                ->take($this->parameter->getLimit())
                ->skip($this->parameter->getOffset())
                ->get();

            if(empty($query)) {
                $results = [];
            } else {
                $results = $query->toArray();
            }

            $this->response->getResponse($results);

        } catch(\Exception $e) {
            formatApiException($e);
        }
    }

    /**
     * getEntity
     *
     * @param $id
     * @return array
     */
    public function getEntity($id)
    {
        // init query
        $query = $this->repository;

        // init fields
        $query = $query->select($this->parameter->getFields());

        // init with
        $query = $query->with($this->parameter->getWith());

        // init wheres
        $query = $query->where('id', '=', $id);

        // init first row
        $query = $query->first();

        if(empty($query)) {
            $results = [];
        } else {
            $results = $query->toArray();
        }

        $this->response->getResponse($results);
    }

    /**
     * createEntity
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function createEntity(Request $request)
    {
        try {

            $data = $this->parameter->getFilteredData($request->all(), true);
            $this->response->getResponse($this->repository->create($data)->toArray());

        } catch (\Illuminate\Database\QueryException $exception) {

            $this->createAndUpdateExceptionsHandler($exception);

        } catch (\Exception $e) {

            // fallback: display raw exception
            d([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 1);

        }
    }

    /**
     * updateEntity
     *
     * @param $id
     * @param Request $request
     * @throws \Exception
     */
    public function updateEntity($id, Request $request)
    {
        try {

            $result = $this->repository->findOrFail($id);

            if($result->update($request->all())) {
                $this->response->getResponse($result->toArray());
            };

        } catch (ModelNotFoundException $e) {

            // exception : object not found
            throw new \Exception('data object matching id '.$id.' not found', '404');

        } catch (\Illuminate\Database\QueryException $exception) {

            $this->createAndUpdateExceptionsHandler($exception);

        } catch (\Exception $e) {

            // fallback: display raw exception
            d([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 1);

        }
    }

    /**
     * deleteEntity
     *
     * @param $id
     * @throws \Exception
     */
    public function deleteEntity($id)
    {
        try {

            $result = $this->repository->findOrFail($id);
            $response = $result->delete();

            if($response) {
                echo json_encode([
                    'data' => true,
                ]);
                exit;
            }

        } catch (ModelNotFoundException $e) {

            // exception : object not found
            throw new \Exception('data object matching id '.$id.' not found', '404');

        } catch (\Exception $e) {

            // fallback: display raw exception
            d([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 1);

        }
    }

    /**
     * createAndUpdateExceptionsHandler
     *
     * @param $exception
     * @throws \Exception
     */
    public function createAndUpdateExceptionsHandler($exception)
    {
        $errorCode = $exception->errorInfo[1];
        $message = $exception->getMessage();

        // exception: duplicate entry / unique key
        if($errorCode == 1062){

            $explode1 = explode('SQLSTATE[23000]: Integrity constraint violation: 1062 ', $message);
            $explode2 = explode('(', $explode1[1]);

            throw new \Exception(trim($explode2[0]), '501');
        }

        // exception: Invalid datetime format
        if($errorCode == 1292){

            $explode1 = explode('SQLSTATE[22007]: Invalid datetime format: 1292 ', $message);
            $explode2 = explode('at row', $explode1[1]);

            throw new \Exception(trim($explode2[0]), '501');
        }

        // exception : Foreign Key ID not found exception
        if($errorCode == 1452) {

            $explode1 = explode('FOREIGN KEY ', $message);
            $explode2 = explode('(SQL', $explode1[1]);
            $response = 'Relationship ID mismatch exception: '.$explode2[0];
            $response = str_replace('))', ')', $response);

            throw new \Exception(trim($response), '501');
        }

        // fallback: display raw exception
        d([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ], 1);
    }
}
