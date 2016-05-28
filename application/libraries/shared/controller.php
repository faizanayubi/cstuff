<?php

/**
 * Subclass the Controller class within our application.
 *
 * @author Faizan Ayubi, Hemant Mann
 */

namespace Shared;

use Framework\Events as Events;
use Framework\Router as Router;
use Framework\Registry as Registry;

class Controller extends \Framework\Controller {

    /**
     * @readwrite
     */
    protected $_user;

    public function seo($params = array()) {
        $seo = Registry::get("seo");
        foreach ($params as $key => $value) {
            $property = "set" . ucfirst($key);
            $seo->$property($value);
        }
        $this->layoutView->set("seo", $seo);
    }

    public function noview() {
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;
    }

    public function JSONview() {
        $this->willRenderLayoutView = false;
        $this->defaultExtension = "json";
    }

    public function redirect($url) {
        $this->noview(); // stop rendering of views because it can generate errors
        header("Location: {$url}");
        exit();
    }

    public function setUser($user) {
        $session = Registry::get("session");
        if ($user) {
            $session->set("user", $user->id);
        } else {
            $session->erase("user");
        }
        $this->_user = $user;
        return $this;
    }

    protected function randomPassword() { 
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    protected function log($message = "") {
        $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
        $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
        $content = "[{$timestamp}] {$message}";
        file_put_contents($logfile, $content, FILE_APPEND);
    }

    /**
     * The method checks whether a file has been uploaded. If it has, the method attempts to move the file to a permanent location.
     * @param string $name
     * @param string $type files or images
     */
    protected function _upload($name, $type = "images") {
        if (isset($_FILES[$name])) {
            $file = $_FILES[$name];
            $path = APP_PATH . "/public/assets/uploads/{$type}/";
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = uniqid() . ".{$extension}";
            if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                return $filename;
            }
        }
        return FALSE;
    }

    protected function sendgrid() {
        $configuration = Registry::get("configuration");
        $parsed = $configuration->parse("configuration/mail");
        $sendgrid = new \SendGrid\SendGrid($parsed->mail->sendgrid->key);
        
        return $sendgrid;
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
        $sendgrid = $this->sendgrid();
        $email = new \SendGrid\Email();
        $email
            ->addTo($emails)
            ->setFrom('CloudStuff <'. $parsed->mail->mailgun->account .'>')
            ->setSubject($options["subject"])
            ->setText($body)
        ;
        $sendgrid->send($email);
        $this->log(implode(",", $emails));
    }

    public function __construct($options = array()) {
        parent::__construct($options);

        // connect to database
        $database = Registry::get("database");
        $database->connect();

        $mongoDB = Registry::get("MongoDB");
        if (!$mongoDB) {
            $mongo = new \MongoClient();
            $mongoDB = $mongo->selectDB("cstuff");
            Registry::set("MongoDB", $mongoDB);
        }

        // schedule: load user from session           
        Events::add("framework.router.beforehooks.before", function($name, $parameters) {
            $session = Registry::get("session");
            $controller = Registry::get("controller");
            $user = $session->get("user");
            if ($user) {
                $controller->user = \Models\User::first(array("id = ?" => $user));
            }
        });

        // schedule: save user to session
        Events::add("framework.router.afterhooks.after", function($name, $parameters) {
            $session = Registry::get("session");
            $controller = Registry::get("controller");
            if ($controller->user) {
                $session->set("user", $controller->user->id);
            }
        });

        // schedule: disconnect from database
        Events::add("framework.controller.destruct.after", function($name) {
            $database = Registry::get("database");
            $database->disconnect();
        });
    }

    /**
     * Checks whether the user is set and then assign it to both the layout and action views.
     */
    public function render() {
        /* if the user and view(s) are defined, 
         * assign the user session to the view(s)
         */
        if ($this->user) {
            if ($this->actionView) {
                $key = "user";
                if ($this->actionView->get($key, false)) {
                    $key = "__user";
                }
                $this->actionView->set($key, $this->user);
            }
            if ($this->layoutView) {
                $key = "user";
                if ($this->layoutView->get($key, false)) {
                    $key = "__user";
                }
                $this->layoutView->set($key, $this->user);
            }
        }
        parent::render();
    }

    public function __destruct() {
        $layoutView = $this->layoutView;
        if ($layoutView && !$layoutView->get("seo")) {
            $layoutView->set("seo", \Framework\Registry::get("seo"));
        }
        parent::__destruct();
    }
}

