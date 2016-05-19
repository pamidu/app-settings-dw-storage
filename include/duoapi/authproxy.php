<?php
	class AuthProxy {

		private $invoker;

		function __construct(){
			$this->invoker = new WsInvoker(SVC_AUTH_URL);
			//$this->invoker->setContentType("application/x-www-form-urlencoded");
		}

		public function GetAccess($token){
			$json = $this->invoker->get("/GetSession/". $token . "/duosoftware.com");
			return json_decode($json);
		}

		public function GetTenantUsers($tid){
			$this->invoker->addHeader("Securitytoken", $_COOKIE["securityToken"]);
			$json = $this->invoker->get("/tenant/GetUsers/" . $tid);
			return json_decode($json);
		}
	}
?>