<?php

/**
 * @author Faizan Ayubi
 */
namespace Models;
class Conversation extends \Shared\Model {
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required
     */
    protected $_ticket_id;

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @validate required, min(3)
     * @label message
     */
    protected $_message;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label file
     */
    protected $_file;
}
