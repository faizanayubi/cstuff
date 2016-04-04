<?php

/**
 * @author Faizan Ayubi
 */

namespace Shared;

use Framework\Events as Events;
use Framework\Router as Router;
use Framework\Registry as Registry;

class Mailgun {

	/**
	 * The Main Method to return Mailgun Instance
	 * 
	 * @return \Mailgun\Mailgun Instance of Mailgun
	 */
	protected function mailgun() {
	    $configuration = Registry::get("configuration");
	    $parsed = $configuration->parse("configuration/mail");

	    if (!empty($parsed->mail->mailgun) && !empty($parsed->mail->mailgun->key)) {
	        $mg = new \Mailgun\Mailgun($parsed->mail->mailgun->key);
	        return $mg;
	    }
	}
	
	protected function getBody($options) {
	    $template = $options["template"];
	    $view = new \Framework\View(array(
	        "file" => APP_PATH . "/application/views/layouts/email/{$template}.html"
	    ));
	    foreach ($options as $key => $value) {
	        $view->set($key, $value);
	    }

	    return $view->render();
	}
	
	protected function notify($options) {
	    $body = $this->getBody($options);
	    $emails = isset($options["email"]) ? array($options["email"]) : array($options["user"]->email);
	    $mailgun = $this->mailgun();
	    $mailgun->sendMessage("cloudstuff.tech",array(
	        'from'    => 'Hemant Mann <hemant@cloudstuff.tech>',
	        'to'      => $emails,
	        'subject' => $options["subject"],
	        'text'    => $body
	    ));
	    $this->log(implode(",", $emails));
	}
}