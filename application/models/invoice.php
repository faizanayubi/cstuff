<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class Invoice extends \Shared\Model {

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
     * @type decimal
     * @length 10,2
     */
    protected $_amount;

    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     */
    protected $_duedate;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @label reference of paid
     */
    protected $_ref;
}
