<?php

namespace Universal\Plugin\Api;

/**
 * Class Response
 *
 * @package Universal\Plugin\Api
 */
class Response
{
    /**
     * getResponse.
     *
     * @param array $results
     */
    public function getResponse($results = [])
    {
        if(isset($results[0])) {
            foreach($results as $resultKey => $resultValue) {
                $this->arrayRearrangeKeys($resultValue);
                $results[$resultKey] = $resultValue;
            }
        } elseif(isset($results['id'])) {
            $this->arrayRearrangeKeys($results);
        }

        echo json_encode([
            'data' => $results
        ]);

        exit;
    }

    /**
     * arrayRearrangeKeys.
     *
     * @param $array
     * arrayRearrangeKeys
     */
    public function arrayRearrangeKeys(&$array)
    {
        if(isset($array['id'])) {
            $this->arrayKeyMoveToTop($array, 'id');
        }

        if(isset($array['created_at'])) {
            $this->arrayKeyMoveToBottom($array, 'created_at');
        }

        if(isset($array['updated_at'])) {
            $this->arrayKeyMoveToBottom($array, 'updated_at');
        }
    }

    /**
     * arrayKeyMoveToTop.
     *
     * @param $array
     * @param $key
     * arrayKeyMoveToTop
     */
    public function arrayKeyMoveToTop(&$array, $key) {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;
    }

    /**
     * arrayKeyMoveToBottom.
     *
     * @param $array
     * @param $key
     * arrayKeyMoveToBottom
     */
    public function arrayKeyMoveToBottom(&$array, $key) {
        $value = $array['created_at'];
        unset($array[$key]);
        $array[$key] = $value;
    }
}
