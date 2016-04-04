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
     * @label name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label address
     */
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label city
     */
    protected $_city;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label postal code
     */
    protected $_postalcode;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required
     * @label country
     */
    protected $_country;
}
