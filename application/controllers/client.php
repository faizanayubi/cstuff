<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;
use Shared\Services\Client as ClientService;

class Client extends Auth {
    /**
     * @readwrite
     */
    protected $_organization;

    /**
     * @protected
     */
    public function _secure() {
        parent::_secure();
        $session = Registry::get("session");

        $org = $session->get("organization");
        if (!$org) {
            $this->redirect("/404");
        }
        $this->_organization = $org;
        $this->setLayout("layouts/client");
    }

    public function setOrganization($org) {
        $session = Registry::get("session");
        if ($org) {
            $session->set("organization", $org);
        } else {
            $session->erase("organization");
        }
        $this->_organization = $org;
        return $this;
    }

    public function logout() {
        $this->setOrganization(false);
        parent::logout();
    }

    /**
     * @before _secure
     */
    public function index() {
    	$this->seo(array("title" => "Main"));
        $view = $this->getActionView();
    }

    /**
     * @before _secure
     */
    public function services() {
    	$this->seo(array("title" => "Services"));
        $view = $this->getActionView();

        $services = ClientService::services($this->user);
        $view->set("services", $services);
    }

    /**
     * @before _secure
     */
    public function account() {
    	$this->seo(array("title" => "Account"));
        $view = $this->getActionView();

        $user = $this->user; $org = $this->organization;
        $user_fields = $user->render(["name", "phone"]);
        $org_fields = $org->render(["name", "address", "city", "postalcode", "country"]);

        $view->set("u_fields", $user_fields)
            ->set("org_fields", $org_fields)
            ->set("errors", []);

        $this->_update($view, $user_fields, $org_fields);
    }

    protected function _update(&$view, $user_fields, $org_fields) {
        $user = $this->user; $org = $this->organization;
        $action = RequestMethods::post("action");
        if ($action == "userUpdate") {
            foreach ($user_fields as $key => $value) {
                $user->$key = RequestMethods::post($key);
            }
            if ($user->validate()) {
                $user->save();
                $this->setUser($user);
            } else {
                $view->set("errors", $user->errors);
                return;
            }
            $view->set("message", "Basic info updated!!");
        }

        if ($action == "orgUpdate") {
            foreach ($org_fields as $key => $value) {
                $org->$key = RequestMethods::post($key);
            }

            if ($org->validate()) {
                $org->save();
                $this->setOrganization($org);   
            } else {
                $view->set("errors", $org->errors);
                return;
            }
            $view->set("message", "Account info updated!!");
        }
    }

    /**
     * @before _secure
     */
    public function invoices() {
    	$this->seo(array("title" => "Invoices"));
        $view = $this->getActionView();

        $invoices = Models\Invoice::all(["user_id = ?" => $this->user->id]);
        $view->set("invoices", $invoices);
    }

    /**
     * @before _secure
     */
    public function orders() {
    	$this->seo(array("title" => "Orders"));
        $view = $this->getActionView();

        $orders = ClientService::orders($this->user);
        $view->set("orders", $orders);
    }

    /**
     * @protected
     */
    public function render() {
        if ($this->layoutView) {
            $this->layoutView->set("organization", $this->organization);
        }

        if ($this->actionView) {
            $this->actionView->set("organization", $this->organization);
        }
        parent::render();
    }

    /**
     * @before _admin
     */
    public function manage() {
        $this->setLayout("layouts/admin");
        $this->seo(array("title" => "Manage Users", "keywords" => "admin", "description" => "admin"));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);

        $property = RequestMethods::get("property", "live");
        $val = RequestMethods::get("value", 1);

        $where = ["{$property} = ?" => $val];
        $users = Models\User::all($where, ["*"], "created", "desc", $limit, $page);
        $count = Models\User::count($where);
        $view->set([
            "users" => $users,
            "page" => $page,
            "limit" => $limit,
            "count" => $count,
            "property" => $property,
            "val" => $val
        ]);
    }
}
