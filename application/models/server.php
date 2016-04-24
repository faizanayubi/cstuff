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
     * @validate required, numeric
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     * @validate required, numeric
     */
    protected $_item_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     * @validate required, numeric
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

    public static function saveRecord($record = null, $opts = []) {
        if (!$record) {
            $record = new self([
                "user_id" => $opts["user_id"],
                "item_id" => $opts["item_id"],
                "service_id" => $opts["service_id"],
                "os" => $opts["os"]
            ]);
        }

        $record->user = (isset($opts["user"])) ? $opts["user"] : "";
        $record->pass = (isset($opts["pass"])) ? $opts["pass"] : "";
        $record->ips = (isset($opts["ips"])) ? $opts["ips"] : "";

        $record->save();
        return $record;
    }
}
