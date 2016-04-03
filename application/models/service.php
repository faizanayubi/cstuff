<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Service extends Shared\Model {

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
    protected $_item_id;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate required
     * @label billing period
     */
    protected $_period;

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

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label type
     */
    protected $_type;


    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     * @label next renewal
     */
    protected $_renewal;
}
