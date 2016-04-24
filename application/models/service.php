<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Service extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     * @validate required
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     *
     * @validate required
     * @label Billing Period
     */
    protected $_period;

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

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label Type
     */
    protected $_type;


    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     * @label Next Renewal
     */
    protected $_renewal;
}
