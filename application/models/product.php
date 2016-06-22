<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class Product extends \Shared\Model {

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
     *
     * @validate required
     * @label Plan
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 10,2
     *
     * @validate required
     * @label Price
     */
    protected $_price;
}
