<?php

/**
 * @author Faizan Ayubi
 */
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
     * @validate required, min(3), max(255)
     * @label title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_details;

}
