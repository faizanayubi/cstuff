<?php

/**
 * Description of meta
 *
 * @author Hemant Mann
 */
namespace Models;
class Meta extends \Shared\Model {
    
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
     * @validate required
     * @label property
     */
    protected $_property;
    
    /**
     * @column
     * @readwrite
     * @type text
     *
     * @validate required
     * @label value
     */
    protected $_value;
}