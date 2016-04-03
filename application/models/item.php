<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class Item extends Shared\Model {

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
     * @label plan
     */
    protected $_plan;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * 
     * @validate required
     * @label processor
     */
    protected $_processor;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_ram;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_disk;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 10,2
     *
     * @validate required
     * @label price
     */
    protected $_price;
}
