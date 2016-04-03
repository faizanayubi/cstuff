<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;

class Account extends Controller {

    public function index() {
    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));
    }

}
