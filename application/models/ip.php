<?php

/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

namespace Models;
class IP extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_server_id;

	/**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate required
     * @label IP address
     */
    protected $_address;    
}
