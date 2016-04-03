<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Server extends Shared\Model {

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
     * @type text
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
     * @type decimal
     * @length 255
     *
     * @label server login pass
     */
    protected $_pass;

}
