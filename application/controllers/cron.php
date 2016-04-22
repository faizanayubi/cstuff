<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Hemant Mann
 */

class CRON extends \Auth {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->noview();
        if (php_sapi_name() != 'cli') {
            $this->redirect("/404");
        }
    }

    public function index($type = "daily") {
        switch ($type) {
            case 'daily':
                $this->_daily();
                break;

            case 'weekly':
                $this->_weekly();
                break;
        }
    }

    protected function _hourly() {
        // implement
    }

    protected function _daily() {
        $this->_removeLogs();
        $this->log("Removed Ping Logs");
    }

    protected function _weekly() {
        // implement
    }

    protected function _removeLogs() {
        $ping = Registry::get("MongoDB")->ping;
        $ping_stats = Registry::get("MongoDB")->ping_stats;
        $records = $ping->find([]);

        foreach ($records as $r) {
            switch ($r['interval']) {
                case 'Daily':
                    $time = strtotime("-20 day");
                    break;
                case 'Hourly':
                    $time = strtotime("-4 day");
                    break;

                case 'Minutely':
                    $time = strtotime("-1 day");
                    break;

                default:
                    $time = strtotime("-4 day");
                    break;
            }
            $ping_stats->remove([
                'ping_id' => $r['_id'],
                'created' => ['$lte' => new \MongoDate($time)]
            ]);
        }
    }
}
