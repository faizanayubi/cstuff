<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Lead extends \Shared\Model {

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
     * @index
     * 
     * @validate required, max(255)
     * @label Email Address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 15
     * 
     * @validate required, max(15)
     * @label Phone Number
     */
    protected $_phone;

}
