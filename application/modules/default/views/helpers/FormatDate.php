<?php
/**
 * Format a date with the right locale
 */
class App_View_Helper_FormatDate
{
    public function formatDate($timestamp, $format = null)
    {
        if (is_string($timestamp)) {
            $date = new Zend_Date($timestamp, Zend_date::TIMESTAMP);
        } else {
            $date = new Zend_Date(time(), Zend_date::TIMESTAMP);
        }
        if (!is_string($format)) {
        	$format = 'MMMM dd, yyyy';
        }
        return $date->get($format, 'en_US');
    }
}