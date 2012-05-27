<?php
/**
 * HTML Purifier Intergration
 * @see http://htmlpurifier.org/
 */
class Zend_Controller_Action_Helper_Purify extends Zend_Controller_Action_Helper_Abstract
{
    function direct($form, $removeArray = array())
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->isPut()) {
            parse_str($request->getRawBody(), $params);
            foreach ($params as $key => $value) {
                $request->setParam($key, $value);
            }
            $form = $request->getParams();
        }
        
        if (sizeof($removeArray)) {
            foreach ($removeArray as $_index => $value) {
                if (array_key_exists($value, $form)) {
                    unset($form[$value]);
                }
            }
        }
        
        $purifier = Zend_Registry::get('purifier');
        
        $data = array();
        foreach ($form as $_index => $value) {
            if (empty($value) && $value != '0') {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $_key => $val) {
                    $data[$_index][$_key] = $purifier->purify($val);
                }
            } else {
                $data[$_index] = $purifier->purify($value);
            }
        }
        
        return $data;
    }
}