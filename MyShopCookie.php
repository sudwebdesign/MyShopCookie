<?php
/**
 * Plugin MyShopCookie
 * @author Yannic
 **/
class MyShopCookie extends plxPlugin {
 public function __construct($default_lang) {
  parent::__construct($default_lang);
  # droits pour accèder à la page config.php du plugin
  $this->setConfigProfil(PROFIL_ADMIN);

  if($this->getParam('localStorage')){
   $this->addHook('plxMyShopPanierCoordsMilieu', 'inlineHtml');
   $this->addHook('plxMyShopPanierFin', 'inlineJs');
  }
  if($this->getParam('cookie')){
   $this->addHook('plxMotorConstruct', 'plxMotorConstruct');
   $this->addHook('IndexEnd', 'IndexEnd');
  }
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

// hook des boutons localStorage du formulaire pour les clients de plxMyShop au milieu du Panier
 public function inlineHtml() { ?>
    <p><span id="bouton_sauvegarder">&nbsp;</span>&nbsp;<span id="bouton_effacer">&nbsp;</span>&nbsp;<span id="bouton_raz">&nbsp;</span></p>
    <p id="alerte_sauvegarder" class="alert green" style="display:none;">&nbsp;</p>
<?php
}

// hook js localStorage du formulaire pour les clients de plxMyShop à la fin du Panier
 public function inlineJs() { ?>
<script type="text/JavaScript">

 if (window.localStorage) {
  function lsTest(){
   var test = "test";
   try {
    localStorage.setItem(test, test);
    localStorage.removeItem(test);
    return true;
   } catch(e) {
    return false;
   }
  }

  if(lsTest() === true){
   function stock(){
    var temp = {
    firstname:document.getElementById("firstname").value,
    lastname:document.getElementById("lastname").value,
    email:document.getElementById("email").value,
    tel:document.getElementById("tel").value,
    adress:document.getElementById("adress").value,
    postcode:document.getElementById("postcode").value,
    city:document.getElementById("city").value,
    country:document.getElementById("country").value,
    };
    localStorage.setItem("Shop_Deliver_Adress", JSON.stringify(temp));
    document.getElementById("alerte_sauvegarder").innerHTML = "<?php echo $this->lang('L_ADDRESS_SAVED'); ?><br /><?php echo $this->lang('L_DO_NOT_SHARED'); ?>";
    document.getElementById("alerte_sauvegarder").style.display = "block";
    setTimeout(function(){
    document.getElementById("alerte_sauvegarder").style.display = "none"; }, 3000);
   }
   function clear(){
    localStorage.removeItem("Shop_Deliver_Adress"); 
    document.getElementById("alerte_sauvegarder").innerHTML = "<?php echo $this->lang('L_ADDRESS_DELETED'); ?>";
    document.getElementById("alerte_sauvegarder").style.display = "block";
    setTimeout(function(){
    document.getElementById("alerte_sauvegarder").style.display = "none"; }, 3000);
   }
   function raz(){
    clear();
    document.getElementById("firstname").value = "";
    document.getElementById("lastname").value = "";
    document.getElementById("email").value = "";
    document.getElementById("tel").value = "";
    document.getElementById("adress").value = "";
    document.getElementById("postcode").value = "";
    document.getElementById("city").value = "";
    document.getElementById("country").value = "";
   }
   var gm =  JSON.parse(localStorage.getItem("Shop_Deliver_Adress"));
   if (gm != null){
    document.getElementById("firstname").value = gm["firstname"];
    document.getElementById("lastname").value = gm["lastname"];
    document.getElementById("email").value = gm["email"];
    document.getElementById("tel").value = gm["tel"];
    document.getElementById("adress").value = gm["adress"];
    document.getElementById("postcode").value = gm["postcode"];
    document.getElementById("city").value = gm["city"];
    document.getElementById("country").value = gm["country"];
   }
   var bouton_un = document.getElementById("bouton_sauvegarder");
   var input_un = document.createElement("input");
   input_un.setAttribute("name","SaveAdress");
   input_un.setAttribute("value","<?php echo $this->lang('L_SAVE_MY_ADDRESS'); ?>");
   input_un.setAttribute("type","button");
   input_un.addEventListener("click",stock, false);
   bouton_un.appendChild(input_un);

   var bouton_deux = document.getElementById("bouton_effacer");
   input_deux = document.createElement("input");
   input_deux.setAttribute("name","ClearAdress");
   input_deux.setAttribute("value","<?php echo $this->lang('L_DELETE_MY_ADDRESS'); ?>");
   input_deux.setAttribute("type","button");
   input_deux.addEventListener("click",clear, false);
   bouton_deux.appendChild(input_deux);

   var bouton_raz = document.getElementById("bouton_raz");
   input_raz = document.createElement("input");
   input_raz.setAttribute("name","RAZAdresse");
   input_raz.setAttribute("value","<?php echo $this->lang('L_RESET_ADDRESS'); ?>");
   input_raz.setAttribute("type","button");
   input_raz.addEventListener("click",raz, false);
   bouton_raz.appendChild(input_raz);
  }
 }
</script>

<?php
 }
}