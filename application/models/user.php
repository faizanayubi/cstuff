<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class User extends Shared\Model {

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
     * @index
     * 
     * @validate required, max(255)
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 15
     * 
     * @validate required, max(15)
     * @label phone number
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * 
     * @label organization
     */
    protected $_organization;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, alpha, min(8), max(100)
     * @label password
     */
    protected $_password;
    
    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_admin = false;

}
