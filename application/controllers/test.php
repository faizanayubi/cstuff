<?php

use \WebBot\lib\WebBot\Bot as Bot;

/**
 * Class to scrape types of VPS provided
 */
class Test extends \Auth {
	
	/**
	 * @before _secure
	 */
	public function index() {
		$this->noview();

		$this->log("Items cron started");
		$bot = new Bot([
			'cloud' => 'https://www.wholesaleinternet.net/dedicated/'
		]);
		$bot->execute();
		$doc = array_shift($bot->getDocuments());
		$el = $doc->query('//*[@id="window"]/table')->item(0);

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
					$item->price = $result['price'];
					$item->ips = $result['ips'];
					$item->bandwidth = $result['bandwidth'];
				}
				$item->live = $result['live'];
				$item->save();
			}
		}
		$this->log("Items cron ended");
	}

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
			} elseif ($length === 5) { // bandwidth column
				$result[$keys[$i]] = $this->_filterInput($child->item(0)->textContent);
				$result[$keys[++$i]] = $this->_filterInput($child->item(2)->textContent);
			} elseif ($length === 3) { // checkout button
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
	 * @protected
	 */
	public function _secure() {
		if (php_sapi_name() !== 'cli') {
            $this->redirect("/404");
        }
	}
}
