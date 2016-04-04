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

        foreach ($services as $s) {
            $item = $items[$s->item_id];
            $results[] = ArrayMethods::toObject([
                "id" => $s->id,
                "name" => $item->plan,
                "type" => $s->type,
                "price" => $s->price,
                "period" => $s->period,
                "renewal" => $s->renewal,
                "created" => $s->created
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

        if (RequestMethods::post("action") == "update") {
            foreach ($user_fields as $key => $value) {
                $user->$key = RequestMethods::post($key);
            }
            $user->save();
            $this->setUser($user);

            foreach ($org_fields as $key => $value) {
                $org->$key = RequestMethods::post($key);
            }
            $org->save();
            $this->setOrganization($org);
        }

        $view->set("u_fields", $user_fields)
            ->set("org_fields", $org_fields);
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
    }

    /**
     * @before _secure
     */
    public function orders() {
    	$this->seo(array("title" => "Orders"));
        $view = $this->getActionView();
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
