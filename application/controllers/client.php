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
        $view = $this->getActionView(); $session = Registry::get("session");

        $is_admin = (boolean) $session->get("admin_user_id");
        $message = $this->_addService($is_admin);

        $view->set($message);
        $services = ClientService::services($this->user);
        $view->set("services", $services)
            ->set("is_admin", $is_admin);
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

        $status = RequestMethods::get("status" , "paid");
        switch ($status) {
            case 'paid':
                $live = true;
                break;

            case 'unpaid':
                $live = false;
                break;
            
            default:
                $live = true;
                break;
        }

        $invoices = Models\Invoice::all(["user_id = ?" => $this->user->id, "live = ?" => $live]);
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

    /**
     * @before _secure
     */
    public function server($server_id) {
        $server = Models\Server::first(["id = ?" => $server_id]);
        if (!$server || $server->user_id != $this->user->id) {
            $this->redirect("/404");
        }

        $this->seo(["title" => "Your Server Info"]);
        $view = $this->getActionView();

        $item = Models\Item::first(["id = ?" => $server->item_id]);

        if (RequestMethods::post("action") == "updateLogin") {
            try {
                $server->user = RequestMethods::post("user", "", "^[a-z_][a-z0-9_-]+$");
                $server->pass = RequestMethods::post("pass", "", "^[a-zA-Z0-9_!@#\$]+$");

                $server->save();
                $view->set("message", "Updated Login");
            } catch (\Exception $e) {
                $view->set("message", "Failed to update the server login");
            }
            
        }
        $view->set("server", $server)
            ->set("fields", $server->render())
            ->set("item", $item);
    }

    /**
     * @before _secure
     */
    public function invoice($invoice_id) {
        $invoice = Models\Invoice::first(["id = ?" => $invoice_id]);
        if (!$invoice) {
            $this->redirect("/404");
        }

        $this->seo(["title" => "View Invoice"]);
        $view = $this->getActionView();

        $result = Shared\Services\Client::invoice($invoice);
        $view->set("invoice", $result);
    }

    /**
     * @before _secure
     */
    public function changePassword() {
        $this->noview();

        $session = Registry::get("session");
        $authorized = $session->get('Authenticate:$done');

        $meta = Models\Meta::first(["user_id = ?" => $this->user->id]);
        if (!$meta) {
            $meta = new Models\Meta(array(
                "user_id" => $this->user->id,
                "property" => "resetpass",
                "value" => uniqid()
            ));
            $meta->save();   
        }
        $redirect = "/auth/resetpassword/". $meta->value;
        $session->set('Authenticate:$redirect', $redirect);

        if (!$authorized) {
            $this->redirect("/auth/authenticate");
        } else {
            $this->redirect($redirect);
        }
    }

    protected function _addService($is_admin) {
        if ($is_admin === false) return [];

        $service = new Models\Service();
        $fields = $service->render();

        if (RequestMethods::post("action") == "addService") {
            foreach ($fields as $key => $value) {
                $service->$key = RequestMethods::post($key);
            }
            $service->user_id = $this->user->id;

            $type = strtolower($service->type);
            $valid = ($type == "server" && !$service->item_id) ? false : true;
            if ($service->validate() && $valid) {
                $service->save();

                $order = new Models\Order([
                    "user_id" => $this->user->id,
                    "service_id" => $service->id
                ]);
                $order->save();

                if ($type == "server") {
                    $server = Models\Server::saveRecord(null, [
                        "item_id" => $service->item_id,
                        "user_id" => $this->user->id,
                        "service_id" => $service->id
                    ]);
                }
                return [
                    "fields" => $fields,
                    "message" => "Service Added Successfully!!"
                ];
            } else {
                return [
                    "message" => "Please Fill the required fields",
                    "fields" => $fields
                ];
            }
        } else {
            return ["fields" => $fields];
        }
    }
}
