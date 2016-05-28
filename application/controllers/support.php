<?php
/**
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Models\Ticket as Ticket;
use Models\User as User;
use Models\Conversation as Conversation;
use Framework\RequestMethods as RequestMethods;

class Support extends Client {
    /**
     * @protected
     * @override
     */
    public function _admin() {
        parent::_secure();
        parent::_admin();
        
        $this->setLayout("layouts/admin");
    }
    
	/**
     * @before _secure
     */
	public function tickets() {
		$this->seo(array("title" => "Support Tickets","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $tickets = Ticket::all(array("user_id = ?" => $this->user->id));
        $view->set("tickets", $tickets);
	}

    /**
     * @before _admin
     */
    public function manageticket() {
        $this->seo(array("title" => "Support Tickets","view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $property = RequestMethods::get("property", "live");
        $value = RequestMethods::get("value", 1);
        $where = array("{$property} = ?" => $value);

        $tickets = Ticket::all($where, array("user_id", "subject", "modified", "live", "id"), "modified", "desc", $limit, $page);
        $count = Ticket::count($where);
        
        $view->set("tickets", $tickets);
        $view->set("page", $page);
        $view->set("limit", $limit);
        $view->set("count", $count);
        $view->set("property", $property);
        $view->set("value", $value);
    }

    /**
     * @before _admin
     */
    public function conversations($ticket_id) {
        $this->seo(array("title" => "Conversation","view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $ticket = Ticket::first(array("id = ?" => $ticket_id));
        $u = User::first(array("id = ?" => $ticket->user_id), array("name", "email", "phone", "id"));

        if (RequestMethods::post("action") == "reply") {
            $conversation = new Conversation(array(
                "user_id" => $this->user->id,
                "ticket_id" => $ticket->id,
                "message" => RequestMethods::post("message"),
                "file" => $this->_upload("file", "files")
            ));
            if ($conversation->validate()) {
                $conversation->save();
                $this->notify(array(
                    "template" => "blank",
                    "subject" => $ticket->subject,
                    "message" => strip_tags($conversation->message),
                    "user" => $u
                ));
                $view->set("message", "Message sent successfully");
            }
        }

        $conversations = Conversation::all(array("ticket_id = ?" => $ticket_id), array("user_id", "message", "created", "file"), "created", "asc");
        
        $view->set("conversations", $conversations);
        $view->set("ticket", $ticket);
        $view->set("u", $u);
    }

    /**
     * @before _secure
     */
    public function reply($ticket_id) {
        $this->seo(array("title" => "Conversation","view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $ticket = Ticket::first(array("id = ?" => $ticket_id));
        if (RequestMethods::post("action") == "reply") {
            $conversation = new Conversation(array(
                "user_id" => $this->user->id,
                "ticket_id" => $ticket->id,
                "message" => RequestMethods::post("message"),
                "file" => $this->_upload("file", "files")
            ));
            if ($conversation->validate()) {
                $conversation->save();
                $ticket->live = 1;
                $ticket->save();
                $view->set("message", "Thank You, we will reply within 24 hours.");
            }
        }
        $conversations = Conversation::all(array("ticket_id = ?" => $ticket_id), array("user_id", "message", "created", "file"), "created", "asc");
        
        $view->set("conversations", $conversations);
        $view->set("ticket", $ticket)
            ->set("errors", array());
    }

	/**
     * @before _secure
     */
	public function create() {
		$this->seo(array("title" => "Create Ticket","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $view->set("errors", array());
        if (RequestMethods::post("action") == "ticket") {
        	$ticket = new Ticket(array(
        		"user_id" => $this->user->id,
        		"subject" => RequestMethods::post("subject"),
        		"live" => true
        	));
        	if($ticket->validate()) {
        		$ticket->save();
        		$conversation = new Conversation(array(
        			"user_id" => $this->user->id,
        			"ticket_id" => $ticket->id,
        			"message" => RequestMethods::post("message"),
        			"file" => $this->_upload("file", "files"),
        		));
                if ($conversation->validate()) {
                    $view->set("message", "Thank You, we will reply within 24 hours.");
                    $conversation->save();
                } else {
                    $view->set("errors", $conversation->getErrors());
                }
        	} else {
                $view->set("errors", $ticket->getErrors());
            }
        }

        
        $view->set("tickets", $tickets);
	}

    public function receive() {
        $this->JSONview();
        $view = $this->getActionView();
        $output = '<pre>'. print_r($_POST, true). '</pre>';
        $this->log($output);

        $email = substr(RequestMethods::post("From"), strpos(RequestMethods::post("From"), "<") + 1, -1);
        if (strlen($email) > 3) {
            $user = User::first(array("email = ?" => $email), array("id"));
            if (!isset($user)) {
                $name = substr(RequestMethods::post("From"), 0, strpos(RequestMethods::post("From"), "<"));
                $user = new User(array(
                    "username" => $name,
                    "name" => $name,
                    "email" => $email,
                    "password" => sha1($this->randomPassword()),
                    "phone" => "",
                    "admin" => 0,
                    "live" => 0
                ));
                $user->save();
            }
            $ticket = Ticket::first(array("subject = ?" => str_replace("Re: ", "", RequestMethods::post("Subject")), "user_id = ?" => $user->id));
            if (!isset($ticket)) {
                $ticket = new Ticket(array(
                    "user_id" => $user->id,
                    "subject" => RequestMethods::post("Subject"),
                    "live" => true
                ));
                $ticket->save();
            } else {
                $ticket->live = 1;
                $ticket->save();
            }
            $conversation = new Conversation(array(
                "user_id" => $user->id,
                "ticket_id" => $ticket->id,
                "message" => RequestMethods::post("body-html"),
                "file" => "",
            ));
            $conversation->save();
            $view->set("success", true);
        }
    }

    /**
     * @before _admin
     */
    public function close($ticket_id, $live) {
        $this->noview();
        $ticket = Ticket::first(array("id = ?" => $ticket_id));
        $ticket->live = $live;
        $ticket->save();
    }
}
