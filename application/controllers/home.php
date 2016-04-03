<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\Controller as Controller;

class Home extends Controller {

    public function index() {
    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));
    }


    public function cart($item_id) {
    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));
    }

}
