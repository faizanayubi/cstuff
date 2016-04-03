<?php

use \WebBot\lib\WebBot\Bot as Bot;

/**
 * Class to scrape types of VPS provided
 */
class Test extends Shared\Controller {

	public function index() {
		$this->noview();

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
				
				$item = Models\Item::first(["plan = ?" => $result['plan'], "user_id = ?" => $this->user->id]);
				if (!$item) {
					$item = new Models\Item(array_merge($result, ['user_id' => $this->user->id]));
				}
				$item->live = $result['live'];
				$item->save();
			}
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
			$child = $c->childNodes; $length = $child->length;
			if (!$length) continue;

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
}
