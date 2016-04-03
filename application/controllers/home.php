<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Home extends Controller {

    public function index() {
    	$this->getLayoutView()->set("seo", Framework\Registry::get("seo"));
    	$view = $this->getActionView();

    	$items = Models\Item::all(array("live = ?" => true));
    	$view->set("items", $items);
    }


    public function cart($item_id) {
		$this->seo(array("title" => "Cart"));
		$view = $this->getActionView();

        $item = Models\Item::first(array("live = ?" => true));
        if (!$item) {
            $this->redirect("/index.html");
        }
        $view->set("item", $item);
    }

}
