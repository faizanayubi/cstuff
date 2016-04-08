<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Server extends \Shared\Model {

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
     * @index
     */
    protected $_service_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label operating system
     */
    protected $_os;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label server login user
     */
    protected $_user;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label server login pass
     */
    protected $_pass;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label Server IP Addresses
     * @value Contains JSON encoded array of IP's
     */
    protected $_ips;

}
