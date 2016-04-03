<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Client extends Auth {

    public function index() {
    	$this->seo(array("title" => "Main"));
        $view = $this->getActionView();
    }

    public function services() {
    	$this->seo(array("title" => "Services"));
        $view = $this->getActionView();
    }

    public function account() {
    	$this->seo(array("title" => "Account"));
        $view = $this->getActionView();
    }

    public function tickets() {
    	$this->seo(array("title" => "Tickets"));
        $view = $this->getActionView();
    }

    public function invoices() {
    	$this->seo(array("title" => "Invoices"));
        $view = $this->getActionView();
    }

    public function orders() {
    	$this->seo(array("title" => "Orders"));
        $view = $this->getActionView();
    }
}
