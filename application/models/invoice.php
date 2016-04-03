<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class Invoice extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_organization_id;

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
    protected $_billing_id;

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
     * @type integer
     */
    protected $_mode;
}
