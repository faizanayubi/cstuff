<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class Order extends \Shared\Model {

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
     * @type integer
     * @index
     */
    protected $_service_id;
}
