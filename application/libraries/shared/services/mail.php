<?php

namespace Shared\Services;
use Framework\Registry as Registry;

/**
 * Static class which sends Mail using different configurations
 * @author Hemant Mann
 */
class Mail {
	/**
	 * Stores the mail conf
	 */
	protected static $_conf = array();

	/**
     * The Main Method to return MailGun Instance
     * 
     * @return object \MailGun\MailGun Instance
     */
    protected static function _mailGun() {
    	if (isset(self::$_conf['mailgun'])) {
    		return self::$_conf['mailgun'];
    	}

        $configuration = Registry::get("configuration");
        $parsed = $configuration->parse("configuration/mail");
        if (!empty($parsed->mail->mailgun) && !empty($parsed->mail->mailgun->key)) {
            $mg = new \Mailgun\Mailgun($parsed->mail->mailgun->key);
            self::$_conf['mailgun'] = $mg;
        }
        
        return self::$_conf['mailgun'];
    }
    
    protected static function _body($options) {
        $template = $options["template"];
        $view = new \Framework\View(array(
            "file" => APP_PATH . "/application/views/layouts/email/{$template}.html"
        ));

        foreach ($options as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }
    
    public static function notify($options) {
        $body = self::_body($options);
        $emails = isset($options["emails"]) ? $options["emails"] : array($options["user"]->email);
        $mailgun = self::_mailgun();
        $mailgun->sendMessage("cloudstuff.tech", array(
            'from'    => 'Hemant Mann <hemant@cloudstuff.tech>',
            'to'      => $emails,
            'subject' => $options["subject"],
            'text'    => $body
        ));
        self::log(implode(",", $emails));
    }

    protected static function log($message = "") {
        $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
        $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
        $content = "[{$timestamp}] {$message}";
        file_put_contents($logfile, $content, FILE_APPEND);
    }
}
