<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Organization extends \Shared\Model {

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
     * @validate required, alpha, min(3), max(255)
     * @label Name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label Address
     */
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label City
     */
    protected $_city;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label Postal Code
     */
    protected $_postalcode;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required
     * @label Country
     */
    protected $_country;
}
