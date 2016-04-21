<?php
use Framework\RequestMethods as RequestMethods;

class Analytics extends \Auth {
	/**
	 * @protected
	 */
	public function _admin() {
        $this->_secure();
        parent::_admin();
        
        $this->setLayout("layouts/admin");
    }
	
	/**
	 * @before _admin
	 */
	public function logs($action = "") {
		$this->seo(array("title" => "Activity Logs", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $name = RequestMethods::get("file");
        if ($action == "unlink" && $name) {
            $file = APP_PATH ."/logs/". $name;
            @unlink($file);
            $view->set("message", "Removed $name");
        }

        $logs = array();
        $path = APP_PATH . "/logs";
        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $item) {
            if (!$item->isDot() && substr($item->getFilename(), 0, 1) != ".") {
                $logs[] = $item->getFilename();
            }
        }
        arsort($logs);

        // find the directory size
        exec('du -h '. $path, $output, $return);
        if ($return == 0) {
            $output = array_pop($output);
            $output = explode("/", $output);
            $size = array_shift($output);
            $size = trim($size);
        } else {
            $size = 'Failed to get size';
        }
        $view->set("size", $size);
        $view->set("logs", $logs);
	}

    /**
     * Find the ping stats for the given URL
     * @before _admin
     */
    public function ping() {
        $this->JSONview(); $view = $this->getActionView();

        $url = (RequestMethods::get("link"));
        if (!$url) {
            $this->redirect("/404");
        }

        $count = 0;
        $stats = Registry::get("MongoDB")->ping_stats;
        $ping = Registry::get("MongoDB")->ping;
        $record = $ping->findOne(array('url' => $url, 'user_id' => (int) $this->user->id));
        if (!$record) {
            $this->redirect("/404");
        }
        $count = $stats->count(array('ping_id' => $record['_id']));

        $cursor = $stats->find(array('ping_id' => $record['_id']));
        $cursor->sort(['created' => -1]);
        $cursor->limit(1);

        foreach ($cursor as $c) {
            $live = $c['latency'];
        }
        $count += $count;

        $view->set("count", $count)
            ->set("status", ($live === false) ? "down" : "up")
            ->set("success", true);
    }
}