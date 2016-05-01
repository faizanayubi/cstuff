<?php

use WebBot\Core\Bot as Bot;
use Framework\RequestMethods as RequestMethods;

/**
 * Class to scrape types of VPS provided
 */
class Items extends \Auth {

	/**
	 * @protected
	 */
	public function _admin() {
		parent::_secure();
		parent::_admin();
		$this->setLayout("layouts/admin");
	}
	
	/**
	 * @before _secure
	 */
	public function cron() {
		$this->noview();

		$this->log("Items cron started");
		Bot::$logging = false;
		$bot = new Bot([
			'cloud' => 'https://www.wholesaleinternet.net/dedicated/'
		]);
		$bot->execute();
		$doc = array_shift($bot->getDocuments());
		$el = $doc->query('//*[@id="window"]/table')->item(0);
		$original_items = Models\Item::all();

		$children = $el->childNodes;
		foreach ($children as $c) {
			if ($c->getAttribute('onclick')) { // tr
				$ch = $c->childNodes;
				$result = $this->_process($ch);
				$result['price'] = $this->_price($result['price']);
				
				$item = Models\Item::first([
					"plan = ?" => $result['plan'],
					"processor = ?" => $result['processor'],
					"ram = ?" => $result['ram'],
					"disk = ?" => $result['disk'],
					"user_id = ?" => 1
				]);
				if (!$item) {
					$item = new Models\Item(array_merge($result, ['user_id' => 1]));
				} else {
					unset($original_items[$item->id]);
					$item->price = $result['price'];
					$item->ips = $result['ips'];
					$item->bandwidth = $result['bandwidth'];
				}
				$item->live = (($result['live'])) ? $result['live'] : '0';
				$item->save();
			}
		}
		foreach ($original_items as $i) {
			$i->live = false;
			$i->save();
		}
		$this->log("Items cron ended");
	}

	/**
	 * @param string $price
	 * @return integer If Valid number else exception will be thrown
	 */
	protected function _price($price) {
		preg_match("/([0-9]+\.?[0-9]+)/", $price, $matches);
		if (isset($matches[1])) {
			$num = 67 * $matches[1];
			$num *= 3;
			return $num;
		} else {
			throw new \Exception("Invalid price");
		}
	}

	/**
	 * @param object \DomNodeList (containing <td> of a row)
	 * @return array
	 */
	protected function _process($children) {
		$result = []; $keys = ['plan', 'processor', 'ram', 'disk', 'bandwidth', 'ips', 'price', 'live'];
		$i = 0;
		foreach ($children as $c) {
			$child = $c->childNodes;
			if (!is_object($child)) continue;
			$length = $child->length;

			if ($length === 1) { // normal values
				$el = $child->item(0);
				if ($el->nodeName == 'div') { // plan + processor
					$result[$keys[$i]] = $el->childNodes->item(0)->textContent;
					$result[$keys[++$i]] = $el->childNodes->item(2)->nodeValue  . ", " .  trim($el->childNodes->item(4)->nodeValue);
				} else {
					$result[$keys[$i]] = $el->textContent;
				}
			} else if ($length === 5) { // bandwidth column
				$result[$keys[$i]] = $this->_filterInput($child->item(0)->textContent);
				$result[$keys[++$i]] = $this->_filterInput($child->item(2)->textContent);
			} else if ($length === 3) { // checkout button
				if ($child->item(0)->nodeName == 'input') {
					if ($child->item(0)->getAttribute('style')) {
						$result[$keys[$i]] = false;
					} else {
						$result[$keys[$i]] = true;
					}
				}
			}
			$i++;
		}
		return $result;
	}

	protected function _filterInput($string) {
		$str = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
		return trim($str);
	}

	/**
	 * @before _admin
	 */
	public function manage() {
		$this->seo(["title" => "Manage Items"]);
		$view = $this->getActionView();

		$items = Models\Item::all([], ["*"], "modified", "desc");
		$view->set("items", $items);
	}

	/**
	 * @before _admin
	 */
	public function update($id, $status) {
		$item = Models\Item::first(["id = ?" => $id]);
		if (!$item) {
			$this->redirect("/404");
		}

		$item->live = (int) $status;
		$item->save();
		$this->redirect(RequestMethods::server("HTTP_REFERER", "/admin"));
	}

	/**
	 * @before _admin
	 */
	public function orders() {
		$this->seo(["title" => "Manage Orders"]);
		$view = $this->getActionView();
		$page = RequestMethods::get("page", 1);
		$limit = RequestMethods::get("limit", 10);

		$orders = Models\Order::all([], ["*"], "created", "desc", $limit, $page);
		$count = Models\Order::count();

		if (RequestMethods::post("action") == "updateServer") {
			$ips = RequestMethods::post("ips");
			$ips = explode(",", $ips);
			$ips = array_map((function ($v) {
				$v = str_replace(" ", '', $v);
				return $v;
			}), $ips);

			$ips = json_encode($ips);

			$server = Models\Server::first(["id = ?" => RequestMethods::post("server")]);
			if (!$server) {
				$view->set("message", "Invalid Server id");
			} else {
				$server->ips = $ips;
				$server->save();
				$view->set("message", "IP Alloted Successfully!!");
			}
		}
		$view->set([
			"orders" => $orders,
			"count" => $count,
			"limit" => $limit,
			"page" => $page
		]);
	}

	/**
	 * @protected
	 */
	public function _secure() {
		if (php_sapi_name() !== 'cli') {
            $this->redirect("/404");
        }
	}
}
