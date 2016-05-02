<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Instamojo extends \Shared\Model {
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_payment_request_id;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 10,2
     */
    protected $_amount;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 64
     */
    protected $_status;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_longurl;
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_order_id;
}
