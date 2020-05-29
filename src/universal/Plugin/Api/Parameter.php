<?php

namespace Universal\Plugin\Api;

/**
 * Class ParameterTrait
 *
 * @package Universal\Plugin\Api
 *
 * This class will convert all incoming url and api query parameters to strings, integers and arrays
 *
 * fields = &fields=field1,field2,field3
 * with   = &with=relation1,relation2,relation3
 * wheres = &wheres=[{"key":"field1","operator":"=","value":"123"},{"key":"field2","operator":"=","value":"123"}]
 * likes  = &likes=[{"key":"field1","operator":"=","value":"123"},{"key":"field2","operator":"=","value":"123"}]
 * orders = &orders={"field1":"asc","field2":"asc","field3":"asc","field4":"asc","field5":"asc"}
 * limit  = &limit=(numeric)
 * page   = &page=(numeric)
 * offset = &offset=(numeric)

 */
class Parameter
{
    /** @var array $getParams */
    public $getParams = [];

    /** @var array $postParams */
    public $postParams = [];

    /** @var array $fields */
    public $fields = [];

    /** @var array $with */
    public $with = [];

    /** @var array $wheres */
    public $wheres = [];

    /** @var array $orders */
    public $orders = [];

    /** @var int $limit */
    public $limit = 10;

    /** @var int $page */
    public $page = 1;

    /** @var int $offset */
    public $offset = 0;

    /** @var array $filteredParams */
    public $filteredParams = ['PHPSESSID', 'token', 'unit-test', 'debug'];

    /**
     * Parameter constructor.
     */
    public function __construct()
    {
        $this->initGetParams();
        $this->initPostParams();

        $this->initFields();
        $this->initWith();

        $this->initWheres();
        $this->initOrders();

        $this->initLimit();
        $this->initPage();
        $this->initOffset();
    }

    /**
     * initGetParams.
     */
    public function initGetParams()
    {
        $get = !empty($_GET) ? $_GET : [];

        $params = array();
        $params = array_merge($params, $get);

        if(!empty($params)) {
            foreach($params as $key => $value) {
                if(in_array($key, $this->getFilteredParams())) {
                    unset($params[$key]);
                }
            }
        }
        $this->setGetParams($params);
    }

    /**
     * initPostParams.
     */
    public function initPostParams()
    {
        $post = !empty($_POST) ? $_POST : [];

        // get put params
        parse_str(file_get_contents('php://input'), $params);

        // merge with post params
        $params = array_merge($params, $post);
        //$params = array_merge($params, Input::json());

        if(!empty($params)) {
            foreach($params as $key => $value) {
                if(in_array($key, $this->getFilteredParams())) {
                    unset($params[$key]);
                }
            }
        }
        $this->setPostParams($params);
    }

    /**
     * initFields.
     */
    public function initFields()
    {
        $params = $this->getGetParams();
        if (isset($params['fields']) && !empty($params['fields'])) {
            $params['fields'] = preg_replace("/[^A-Za-z,_0-9.*]/", '', $params['fields']);
            $params['fields'] = array_values(array_filter(explode(',', $params['fields'])));
            $this->setFields($params['fields']);
        } else {
            $this->setFields(['*']);
        }
    }

    /**
     * initWith
     */
    public function initWith()
    {
        $params = $this->getGetParams();
        if (isset($params['with']) && !empty($params['with'])) {
            $params['with'] = preg_replace("/[^A-Za-z,_0-9.]/", '', $params['with']);
            $params['with'] = array_values(array_filter(explode(',', $params['with'])));
            $this->setWith($params['with']);
        }
    }

    /**
     * initWheres.
     *
     * @throws \Exception
     */
    public function initWheres()
    {
        $data = array();
        $params = $this->getGetParams();

        if (isset($params['wheres']) && !empty($params['wheres'])) {

            if(!$this->isJson($params['wheres'])) {
                throw new \Exception('wheres param is not in valid json format');
            }

            $wheres = json_decode($params['wheres'], 1);

            if (is_array($wheres) && !empty($wheres)) {
                foreach ($wheres as $result) {

                    $result['key'] = isset($result['key']) ? $this->filterKey($result['key']) : '';
                    $result['operator'] = isset($result['operator']) ? $this->filterOperator($result['operator']) : '';
                    $result['value'] = isset($result['value']) ? $result['value'] : '';

                    if (!empty($result['key']) && !empty($result['operator'])) {
                        $data[] = $result;
                    }

                }
            }

            $this->setWheres($data);

        }
    }

    /**
     * initOrders.
     */
    public function initOrders()
    {
        $params = $this->getGetParams();
        if (isset($params['orders']) && !empty($params['orders'])) {
            $params['orders'] = json_decode($params['orders'], true);

            $data = array();
            if (is_array($params['orders']) && !empty($params['orders'])) {
                foreach ($params['orders'] as $key => $value) {
                    $key = preg_replace("/[^A-Za-z_0-9.]/", '', $key);
                    $value = preg_replace("/[^A-Za-z_0-9]/", '', $value);

                    if (!in_array($value, array('asc', 'desc'))) {
                        $value = 'asc';
                    }
                    $data[$key] = $value;
                }
            }
            $this->setOrders($data);
        }
    }

    /**
     * initLimit.
     */
    public function initLimit()
    {
        $params = $this->getGetParams();
        if (isset($params['limit']) && !empty($params['limit'])) {
            $limit = isset($params['limit']) ? $params['limit'] : $this->getLimit();
            $limit = preg_replace("/[^0-9]/", '', $limit);

            if($limit > 50) {
                $limit = 50;
            }

            $this->setLimit((int)$limit);
        }
    }

    /**
     * initPage.
     */
    public function initPage()
    {
        $params = $this->getGetParams();
        if (isset($params['page']) && !empty($params['page'])) {
            $page = isset($params['page']) ? $params['page'] : $this->getPage();
            $page = preg_replace("/[^0-9]/", '', $page);
            $this->setPage((int)$page);
            $this->setOffset(($page * $this->limit) - $this->limit);
        }
    }

    /**
     * initOffset.
     */
    public function initOffset()
    {
        $params = $this->getGetParams();
        if (isset($params['offset']) && !empty($params['offset'])) {
            $offset = isset($params['offset']) ? $params['offset'] : $this->getOffset();
            $offset = preg_replace("/[^0-9]/", '', $offset);
            $this->setOffset((int)$offset);
        }
    }

    /**
     * isJson.
     *
     * @param $string
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    /**
     * filterKey.
     *
     * @access public
     * @param $key
     * @return mixed
     */
    public function filterKey($key)
    {
        return preg_replace("/[^A-Za-z_0-9.*]/", '', $key);
    }

    /**
     * filterOperator.
     *
     * @access public
     * @param $operator
     * @return mixed
     */
    public function filterOperator($operator)
    {
        if (in_array($operator, array('=', '<', '<=', '>', '>=', 'like', 'not like', '!=', '==', '==='))) {
            return $operator;
        }
    }

    /**
     * getFilteredData.
     *
     * @param $baseRepository
     * @param $data
     * @param bool $removeId
     * @return mixed
     */
    public function getFilteredData($baseRepository, $data, $removeId = false)
    {
        if(!empty($data)) {
            foreach($data as $key => $value) {
                if(!in_array($key, $baseRepository->getFields())) {
                    unset($data[$key]);
                }
            }
        }

        if($removeId) {
            unset($data['id']);
        }

        return $data;
    }


    /**
     * getGetParams.
     *
     * @return array
     */
    public function getGetParams(): array
    {
        return $this->getParams;
    }

    /**
     * setGetParams.
     *
     * @param array $getParams
     */
    public function setGetParams(array $getParams): void
    {
        $this->getParams = $getParams;
    }

    /**
     * getPostParams.
     *
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->postParams;
    }

    /**
     * setPostParams.
     *
     * @param array $postParams
     */
    public function setPostParams(array $postParams): void
    {
        $this->postParams = $postParams;
    }

    /**
     * getFields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * setFields.
     *
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * getWith.
     *
     * @return array
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * setWith.
     *
     * @param array $with
     */
    public function setWith(array $with): void
    {
        $this->with = $with;
    }

    /**
     * getWheres.
     *
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * setWheres.
     *
     * @param array $wheres
     */
    public function setWheres(array $wheres): void
    {
        $this->wheres = $wheres;
    }

    /**
     * getOrders.
     *
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * setOrders.
     *
     * @param array $orders
     */
    public function setOrders(array $orders): void
    {
        $this->orders = $orders;
    }

    /**
     * getPage.
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * setPage.
     *
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * getLimit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * setLimit.
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * getFilteredParams.
     *
     * @return array
     */
    public function getFilteredParams(): array
    {
        return $this->filteredParams;
    }

    /**
     * setFilteredParams.
     *
     * @param array $filteredParams
     */
    public function setFilteredParams(array $filteredParams): void
    {
        $this->filteredParams = $filteredParams;
    }
}
