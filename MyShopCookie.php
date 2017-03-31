<?php
/**
 * Plugin MyShopCookie
 * @author	Yannic
 **/
class MyShopCookie extends plxPlugin {
	public function __construct($default_lang) {
		parent::__construct($default_lang);
		
		$this->addHook('plxMotorConstruct', 'plxMotorConstruct');
		$this->addHook('IndexEnd', 'IndexEnd');
	}
	public function IndexEnd() {

		$string = '
		// MyShopCookie';
		if(isset($_SESSION["plxMyShop"]["prods"])) {

			// localhost pour test ou véritable domaine ?
			$domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;

			// Durée de vie cookie = fin de session par défaut
			$temps_du_cookie = 0;

			// Durée de vie du cookie = 2 mois si au moins un produit dans le panier
			if (isset($_SESSION["plxMyShop"]["ncart"]) && $_SESSION["plxMyShop"]["ncart"]>0)
				$temps_du_cookie = time() + 3600 * 24 * 30 * 2;

			$string .= '
			if(isset($_SESSION["plxMyShop"])) {
			$cookie_path = "/";
			$cookie_domain = "'.$domain.'";
			$cookie_secure = 0;
			$cookie_expire = '.$temps_du_cookie.';
			$cookie_value["prods"]=preg_replace("/[^0-9]/","",$_SESSION["plxMyShop"]["prods"]);
			$cookie_value["ncart"]=intval($_SESSION["plxMyShop"]["ncart"]);
			if (version_compare(PHP_VERSION, "5.2.0", ">="))
				setcookie("plxMyShop", json_encode($cookie_value), $cookie_expire, $cookie_path, $cookie_domain, $cookie_secure, true);
			else
				setcookie("plxMyShop", serialize($cookie_value), $cookie_expire, $cookie_path."; HttpOnly", $cookie_domain, $cookie_secure);
			}';
		}
		echo "<?php ".$string." ?>";
	}
	public function plxMotorConstruct() {
		$string = '
		// MyShopCookie
		if(!empty($_COOKIE["plxMyShop"]) && !isset($_SESSION["IS_NOT_NEW"])) {
			if (version_compare(PHP_VERSION, "5.2.0", ">="))
				$cookie_value = json_decode($_COOKIE["plxMyShop"],true);
			else
				$cookie_value = unserialize($_COOKIE["plxMyShop"]);				
			$_SESSION["plxMyShop"]["prods"] = preg_replace("/[^0-9]/","",$cookie_value["prods"]);
			$_SESSION["plxMyShop"]["ncart"] = intval($cookie_value["ncart"]);
		} $_SESSION["IS_NOT_NEW"]=true;';
		echo "<?php ".$string." ?>";
	}
}
?>