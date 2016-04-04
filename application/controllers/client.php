<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;

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

        $services = $this->_services();
        $view->set("services", $services);
    }

    protected function _services() {
        $services = Models\Service::all(["user_id = ?" => $this->user->id]);
        $items = Models\Item::all(["user_id = ?" => $this->user->id], ["plan", "id"]);

        $results = [];
        foreach ($services as $s) {
            $item = $items[$s->item_id];
            $results[$s->id] = ArrayMethods::toObject([
                "id" => $s->id,
                "name" => $item->plan,
                "type" => $s->type,
                "price" => $s->price,
                "period" => $s->period,
                "renewal" => $s->renewal,
                "created" => $s->created,
                "live" => $s->live
            ]);
        }
        return $results;
    }

    /**
     * @before _secure
     */
    public function account() {
    	$this->seo(array("title" => "Account"));
        $view = $this->getActionView();

        $user = $this->user;
        $user_fields = $user->render(["name", "phone"]);
        $org = $this->organization;
        $org_fields = $org->render(["name", "address", "city", "postalcode", "country"]);

        $view->set("u_fields", $user_fields)
            ->set("org_fields", $org_fields)
            ->set("errors", []);

        if (RequestMethods::post("action") == "update") {
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

            $view->set("success", "Account info updated!!");
        }
    }

    /**
     * @before _secure
     */
    public function tickets() {
    	$this->seo(array("title" => "Tickets"));
        $view = $this->getActionView();
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

        $orders = $this->_orders();
        $view->set("orders", $orders);
    }

    protected function _orders() {
        $orders = Models\Order::all(["user_id = ?" => $this->user->id], ["id", "service_id", "live"]);
        $services = $this->_services();

        $results = [];
        foreach ($orders as $o) {
            $s = $services[$o->service_id];
            unset($s->id); unset($s->live);
            $d = [
                "id" => $o->id,
                "live" => $o->live
            ];
            $results[$o->id] = (object) array_merge($d, (array) $s);
        }
        return $results;
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
}
