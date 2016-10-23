<?php

namespace TurfWars;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\BlockBreakEvent;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\SignChangeEvent;


use pocketmine\tile\Sign;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;





use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;


use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{

public $MAX = 2;
public $PREFIX = "§d[§9TW§d]§b ";
public $REDSPAWN;
public $BLUESPAWN;
public $config;
public $sign;

public $games = ["Game1" => ["Arena" => "TW-1", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game2" => ["Arena" => "TW-2", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game3" => ["Arena" => "TW-3", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game4" => ["Arena" => "TW-4", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game5" => ["Arena" => "TW-5", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game6" => ["Arena" => "TW-6", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game7" => ["Arena" => "TW-7", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game8" => ["Arena" => "TW-8", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game9" => ["Arena" => "TW-9", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0], "Game10" => ["Arena" => "TW-10", "Status" => "JOINABLE", "RedScore" => 0, "BlueScore" => 0]];



public function onEnable(){
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this), 20);
 


if(!is_dir($this->getServer()->getDataPath()."worlds/TW-1")){

$this->copymap($this->getDataFolder() . "/maps/TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/TW-1");
$this->getLogger()->info("A TurfWars map was added to the worlds folder, you can add more by copy and rename it to TW-2, TW-3, TW-4...");

}



if(!file_exists($this->getDataFolder()."config.yml")){

$this->getServer()->getLogger("No config.yml config found, disabling plugin.");
$this->getServer()->getPluginManager()->disablePlugin($this);

}

$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);



$this->MAX = $this->config->get("Max_Players");
$this->PREFIX = $this->config->get("Prefix")." ";

// initialize spawns
$this->BLUESPAWN = ["x" => 608, "y" => 64, "z" => 1689];
$this->REDSPAWN = ["x" => 534, "y" => 64, "z" => 1665];
}

/* API */





public function onDisable(){


foreach($this->games as $game => $index){
if($index["Status"] == "INGAME"){

$this->deleteDirectory($this->getServer()->getDataPath() . "/worlds/" .$index["Arena"]);
                $this->copymap($this->getDataFolder() . "/maps/TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/" . $index["Arena"]);

}}
}





public function SignUpdate(){

$lobby = $this->getServer()->getDefaultLevel();

if($this->getServer()->isLevelLoaded($lobby->getFolderName())){

foreach($lobby->getTiles() as $tile){


$signb = $this->getServer()->getDefaultLevel()->getBlock(new Vector3($tile->x, $tile->y, $tile->z));


if($signb->getID() == 323 || $signb->getID() == 63 || $signb->getID() == 68){


$sign = $tile;

$signt = $sign->getText();  

if($signt[0] == "§d[§9TW§d]"){        


$levelname = str_replace("§b", "", $signt[1]);

$Status = $this->games[$this->getGameByLevel($levelname)]["Status"];


if($Status > 5 || $Status == "JOINABLE") $st = "§fJoinable"; else $st = "§cIngame";

if($this->getServer()->isLevelLoaded($levelname)){

$players = $this->getServer()->getLevelByName($levelname)->getPlayers();

if(!isset($players)){
$sign->setText($signt[0], $signt[1], $st, "§d0§e / §d".$this->MAX);
}


if(count($players) < 2 && $signt[2] == "§cIngame"){

$sign->setText($signt[0], $signt[1], $st, "§d0§e / §d".$this->MAX);


}else{

$sign->setText($signt[0], $signt[1], $st, "§d".count($players)."§e / §d".$this->MAX);
}

}else{

$sign->setText($signt[0], $signt[1], $st, "§d0§e / §d".$this->MAX);

}}}
}}
}

                        
                        
                        

                      

  
                        
                        
          








public function onQuit(PlayerQuitEvent $event){
if($this->inTurfWars($event->getPlayer())){

$s = $this->games[$this->getGameByPlayer($event->getPlayer())]["Status"];



$this->LeaveCheck($this->getGameByPlayer($event->getPlayer()) , $this->getTeam($event->getPlayer()));


}}



public function updateTerrain($game, $scorerteam){

$x = 570 + $this->games[$game]["RedScore"] - $this->games[$game]["BlueScore"];

$lvl = $this->getServer()->getLevelByName($this->games[$game]["Arena"]);

if($scorerteam == "Red"){

$lvl->setBlock(new Vector3($x, 63, 1661), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1662), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1663), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1664), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1665), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1666), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1667), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1668), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1669), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1670), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1670), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1671), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1672), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1673), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1674), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1675), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1676), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1677), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1678), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1679), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1680), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1681), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1682), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1683), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1684), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1685), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1686), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1687), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1688), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1689), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1690), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1691), Block::get(159, 14));
$lvl->setBlock(new Vector3($x, 63, 1692), Block::get(159, 14));
}



if($scorerteam == "Blue"){

$lvl->setBlock(new Vector3($x + 1, 63, 1661), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1662), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1663), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1664), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1665), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1666), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1667), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1668), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1669), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1670), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1670), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1671), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1672), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1673), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1674), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1675), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1676), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1677), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1678), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1679), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1680), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1681), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1682), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1683), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1684), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1685), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1686), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1687), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1688), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1689), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1690), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1691), Block::get(159, 3));
$lvl->setBlock(new Vector3($x + 1, 63, 1692), Block::get(159, 3));


}


}








public function LeaveCheck($game, $team){


$gameplayers = $this->getServer()->getLevelByName($this->games[$game]["Arena"])->getPlayers();

$s = $this->games[$game]["Status"];

if($s > 5 && count($gameplayers) < 3){
$this->games[$game]["Status"] = "JOINABLE";
return;
}



if($s === "INGAME" || $s === 5 || $s === 4 || $s === 3 || $s === 2 || $s === 1 || $s === "zero"){


$red = 0;
$blue = 0;

if($team == "Red"){
$red = -1;
}else{
$red = 0;
}


if($team == "Blue"){
$blue = -1;
}else{
$blue = 0;
}


foreach($gameplayers as $player){
if($this->getTeam($player) == "Red"){
$red++;
}
if($this->getTeam($player) == "Blue"){
$blue++;
}
}


if(count($gameplayers) < 3 || $blue == 0 || $red == 0){

$this->games[$game]["RedScore"] = 0;
$this->games[$game]["BlueScore"] = 0;
$this->games[$game]["Status"] = "JOINABLE";



foreach($gameplayers as $player){


$player->getInventory()->clearAll();
$player->setNameTag($player->getName());
$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
$player->sendPopup(" ");
$player->sendMessage($this->PREFIX."Game was cancelled, to less players.");

}



$this->deleteDirectory($this->getServer()->getDataPath() . "/worlds/" .$this->games[$game]["Arena"]);
                $this->copymap($this->getDataFolder() . "/maps/TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/" . $this->games[$game]["Arena"]);

}}


}








public function removeArrow(ProjectileHitEvent $event){
if($this->inTurfWars($event->getEntity()->shootingEntity)){
        if($event->getEntity() instanceof Arrow){
            if($event->getEntity()->onGround || $event->getEntity()->inBlock || $event->getEntity()->isCollided){
                $event->getEntity()->close();
            }

         }
        }
    }







public function onSignCreate(SignChangeEvent $event){
if($event->getPlayer()->isOp()){
if($event->getLine(0) == "§d[§9TW§d]"){



if(!$this->getServer()->isLevelGenerated($event->getLine(1))){
$event->getPlayer()->sendMessage($this->PREFIX."The level does not exist.");

$event->setCancelled();
return;
}

$event->setLine(1, "§b".$event->getLine(1));
$event->setLine(2, "§fJoinable");
$event->setLine(3, "§d0§e / §d".$this->MAX);


$event->getPlayer()->sendMessage($this->PREFIX."Join sign was succesfuly created.");


}
}else{
$event->getPlayer()->sendMessage($this->PREFIX."§cYou need to be an OP to create a join sign for that minigame.");
$event->setCancelled();
}
}












public function onInteract(PlayerInteractEvent $event){




if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){

         

$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());

if(!is_null($sign)){


$signt = $sign->getText();
           
 if($signt[0] == "§d[§9TW§d]"){



// remove the color text from the world name
$signt[1] = str_replace("§b", "", $signt[1]);




if($signt[2] != "§fJoinable"){
$event->getPlayer()->sendMessage($this->PREFIX."The game is already full.");
return;
}


// check if the level exists
if($this->getServer()->isLevelGenerated($signt[1])){
$this->joinGame($event->getPlayer(), str_replace("§b", "", $signt[1]));


// refresh sign text
$s1 = str_replace("§", "", $signt[3]);
$s2 = str_replace(" ", "", $s1);
$s3 = str_replace("/", "", $s2);
$s4 = str_replace("e", "", $s3);
$s5 = str_replace("d", "", $s4);
$am = str_replace($this->MAX, "", $s5);

$am = $am + 1;

if($am != $this->MAX){
$sign->setText($signt[0], "§b".$signt[1], $signt[2], "§d".$am."§e / §d".$this->MAX);
}

if($am == $this->MAX){
$sign->setText($signt[0], "§b".$signt[1], "§cIngame", "§d".$am."§e / §d".$this->MAX);

}


}else{
$event->getPlayer()->sendMessage($this->PREFIX."This game is not in usage.");
}


   }        
    }}}

    
    









public function onLaunch(ProjectileLaunchEvent $event){

if($this->inTurfWars($player = $event->getEntity()->shootingEntity)){
if($this->games[$this->getGameByPlayer($player)]["Status"] != "INGAME"){

$player->getInventory()->setItem(1, Item::get(262, 0, 5));
$event->setCancelled();

}else{


$player->getInventory()->setItem(1, Item::get(262, 0, 5));

// add arrow speed for PC experience

if($this->config->get("Enable_PC_Arrow_Mechanics")){
$event->getEntity()->setMotion(new Vector3(- \sin ( $player->yaw / 180 * M_PI ) * \cos ( $player->pitch / 180 * M_PI), - \sin ( $player->pitch / 180 * M_PI), \cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI )));

	$s = 3.75;
		
		$event->getEntity()->setMotion($event->getEntity()->getMotion()->multiply($s));

}


}}
}







public function onItemDrop(PlayerDropItemEvent $event){
if($this->inTurfWars($event->getPlayer())){
$event->setCancelled();
}}




public function onBreak(BlockBreakEvent $event){
if($this->inTurfWars($event->getPlayer())){
$event->setCancelled();
}


 if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){

if(!$event->getPlayer()->isOp()){
$tile = $this->getServer()->getDefaultLevel()->getTile($event->getBlock());
if($tile->getText()[0] == "§d[§9TW§d]"){
$event->setCancelled();
return;
}}



$tile = $this->getServer()->getDefaultLevel()->getTile($event->getBlock());

if($tile->getText()[0] == "§d[§9TW§d]"){

$event->getPlayer()->sendMessage($this->PREFIX."Join sign was removed.");


}}}







public function onMove(PlayerMoveEvent $event){

if($this->inTurfWars($player = $event->getPlayer())){


if(!($this->games[$this->getGameByPlayer($player)]["Status"] == "INGAME" || $this->games[$this->getGameByPlayer($player)]["Status"] == "JOINABLE" || $this->games[$this->getGameByPlayer($player)]["Status"] > 5)){

$event->setCancelled();

}

$block = $event->getPlayer()->getLevel()->getBlock(new Vector3($player->x, 63, $player->z));


if($this->getTeam($player) == "Red"){

if($block->getId() == 159 && $block->getDamage() == 3){



$player->setMotion(new Vector3(-1.5, 1, 0));

}
}


if($this->getTeam($player) == "Blue"){

if($block->getId() == 159 && $block->getDamage() == 14){


$player->setMotion(new Vector3(1.5, 1, 0));

}}}
}







public function onDamage(EntityDamageEvent $event){

if($event instanceof EntityDamageByEntityEvent){


if($event->getCause() == EntityDamageByEntityEvent::CAUSE_PROJECTILE){

$killer = $event->getDamager();
$victim = $event->getEntity();


if($this->inTurfWars($victim)){

if($this->getTeam($victim) == $this->getTeam($killer)){
$event->setCancelled();
return;
}




if(!$killer instanceof Arrow){

if($this->getTeam($killer) == $this->getTeam($victim)){
$event->setCancelled();
return;
}



$this->addScore($this->getTeam($killer), $this->getGameByPlayer($killer));
$this->updateTerrain($this->getGameByLevel($event->getEntity()->getLevel()->getFolderName()), $this->getTeam($killer));



$victim->sendMessage($this->PREFIX."§dYou were killed by §c".$killer->getName());

$killer->sendMessage($this->PREFIX."§dYou killed §a".$victim->getName());

$event->setCancelled();


if($this->getTeam($victim) == "Red"){
$victim->teleport(new Vector3($this->REDSPAWN["x"], $this->REDSPAWN["y"], $this->REDSPAWN["z"]));
$victim->setMotion(new Vector3(0, 0.1, 0));

}elseif($this->getTeam($victim) == "Blue"){
$victim->teleport(new Vector3($this->BLUESPAWN["x"], $this->BLUESPAWN["y"], $this->BLUESPAWN["z"]));
$victim->setMotion(new Vector3(0, 0.1, 0));
}}
}}
}else{
if($event->getEntity() instanceof Player){
if($this->inTurfWars($event->getEntity())){
$event->setCancelled();
}}

}
if($this->inTurfWars($event->getEntity())){
$event->setCancelled();
}
 }





public function joinGame($player, $levelname){

if(!$this->getServer()->isLevelLoaded($levelname)){
$this->getServer()->loadLevel($levelname);
$this->getServer()->getLevelByName($levelname)->setTime(0);
$this->getServer()->getLevelByName($levelname)->stopTime();

}
if($this->games[$this->getGameByLevel($levelname)]["Status"] == "JOINABLE" || $this->games[$this->getGameByLevel($levelname)]["Status"] > 5){

$this->GameSetup($player, $levelname);
}else{
$player->sendMessage($this->PREFIX."Game is running already.");
}}







public function joinRandomGame($player){

foreach($this->games as $game => $value){

$Arena = $value["Arena"];
$Status = $value["Status"];

if($this->getServer()->isLevelGenerated($Arena)){

if(!$this->getServer()->isLevelLoaded($Arena)){
$this->getServer()->loadLevel($Arena);
$this->getServer()->getLevelByName($Arena)->setTime(0);
$this->getServer()->getLevelByName($Arena)->stopTime();
}

if(count($this->getServer()->getLevelByName($Arena)->getPlayers()) < $this->MAX){

$this->GameSetup($player, $Arena /* string*/);
return;

}}}
}











public function GameSetup($player, $Arena){
// Game setup

$Players = $this->getServer()->getLevelByName($Arena)->getPlayers();


// RELOAD AND RESTORE LEVEL

if(count($Players) == 0){
if($this->getServer()->isLevelLoaded($Arena)){
$this->getServer()->unloadLevel($this->getServer()->getLevelByName($Arena));
}}

$this->getServer()->loadLevel($Arena);
// RESTORING END

$player->getInventory()->clearAll();
$player->getInventory()->addItem(Item::get(261, 0, 1));
$player->getInventory()->addItem(Item::get(262, 0, 5));

$player->teleport(new Position(573, 83, 1648, $this->getServer()->getLevelByName($Arena)));

$player->sendMessage($this->PREFIX."Sent you to §a".$Arena);
$player->sendMessage($this->PREFIX."Bow and Arrows were added to your inventory, open it to select them.");




// teleport player to spawn


if(count($this->getServer()->getLevelByName($Arena)->getPlayers()) == 2/*$this->maps->get($Arena."_max")*/){

$this->games[$this->getGameByLevel($Arena)]["Status"] = 80;




 // countdown
}

if(count($this->getServer()->getLevelByName($Arena)->getPlayers()) == $this->MAX){

$this->StartGame($Arena);

$this->games[$this->getGameByLevel($Arena)]["Status"] = 5;



}



}

/* GAME API */




public function StartGame($Arena){



$players = $this->getServer()->getLevelByName($Arena)->getPlayers();

$len = count($players);


$blues = array_slice($players, $len / 2);
$reds = array_slice($players, 0, $len / 2);



foreach($blues as $blue){

$this->setTeam($blue, "Blue");
$blue->sendMessage($this->PREFIX."Your Team: §lBlue");


$customColor = 0x003333ff;

    $i= Item::get(299);

    $tempTag = new CompoundTag("", []);

    $tempTag->customColor = new IntTag("customColor", $customColor);

    $i->setCompoundTag($tempTag);

    $blue->getInventory()->setHelmet($i);
    $blue->getInventory()->setChestplate($i);
    $blue->getInventory()->setLeggings($i);
    $blue->getInventory()->setBoots($i);


$blue->teleport(new Position($this->BLUESPAWN["x"], $this->BLUESPAWN["y"], $this->BLUESPAWN["z"], $this->getServer()->getLevelByName($Arena)));

}


foreach($reds as $red){

$this->setTeam($red, "Red");
$red->sendMessage($this->PREFIX."Your Team: §l§cRed");


$customColor = 0x00ff3300;

    $i = Item::get(299);

    $tempTag = new CompoundTag("", []);

    $tempTag->customColor = new IntTag("customColor", $customColor);

    $i->setCompoundTag($tempTag);

    $red->getInventory()->setHelmet($i);
    $red->getInventory()->setChestplate($i);
    $red->getInventory()->setLeggings($i);
    $red->getInventory()->setBoots($i);



$red->teleport(new Position($this->REDSPAWN["x"], $this->REDSPAWN["y"], $this->REDSPAWN["z"], $this->getServer()->getLevelByName($Arena)));

}


}






public function setTeam($player, $teamname){

if($teamname == "Red"){
$player->setNameTag("§c[RED] §e".$player->getName());
}


if($teamname == "Blue"){
$player->setNameTag("§b[BLUE] §e".$player->getName());
}}





public function getTeam($player){
if(strpos($player->getNameTag(), "[RED] ")){ return "Red";
}elseif(strpos($player->getNameTag(), "[BLUE] ")){ return "Blue";
}}





public function inTurfWars($player){
if($this->getGameByPlayer($player) == ""){
return false;
}else{
return true;
}}





public function getGameByPlayer($player){
if(is_null($player)) return;
foreach($this->games as $game => $value){
if($value["Arena"] == $player->getLevel()->getFolderName()){
return $game;
}}}




public function getGameByLevel($level){
foreach($this->games as $game => $value){
if($value["Arena"] == $level){
return $game;
}}}






public function addScore($team, $game /* Game1, Game2 etc...*/){
$levelname = $this->games[$game]["Arena"];
$level = $this->getServer()->getLevelByName($levelname);
if($team == "Red"){

$this->games[$game]["RedScore"] = $this->games[$game]["RedScore"] + 1;

}elseif($team == "Blue"){


$this->games[$game]["BlueScore"] = $this->games[$game]["BlueScore"] + 1; 

}


if($this->games[$game]["RedScore"] - $this->games[$game]["BlueScore"] == 32){
foreach($this->getServer()->getLevelByName($this->games[$game]["Arena"])->getPlayers() as $player){

if($this->getTeam($player) == "Red"){
$player->sendMessage($this->PREFIX." Your team won.");

}

$player->getInventory()->clearAll();
$player->setNameTag($player->getDisplayName());
$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
$this->games[$game]["Status"] = "JOINABLE";

$player->sendMessage($this->PREFIX."§cRed§b team won.");



}
$this->games[$game]["RedScore"] = 0;
$this->games[$game]["BlueScore"] = 0;


$this->deleteDirectory($this->getServer()->getDataPath() . "/worlds/" .$this->games[$game]["Arena"]);
                $this->copymap($this->getDataFolder() . "/maps/TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/" . $this->games[$game]["Arena"]);







}



if($this->games[$game]["BlueScore"] - $this->games[$game]["RedScore"] == 32){
foreach($this->getServer()->getLevelByName($this->games[$game]["Arena"])->getPlayers() as $player){




$player->sendMessage($this->PREFIX." You got 4 coins for participation.");
$this->getServer()->getPluginManager()->getPlugin("Auth")->addCoins($player, 4);


$player->getInventory()->clearAll();
$player->setNameTag($player->getDisplayName());
$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());


$player->sendMessage($this->PREFIX."§bBlue team won.");



}
$this->games[$game]["Status"] = "JOINABLE";
$this->games[$game]["RedScore"] = 0;
$this->games[$game]["BlueScore"] = 0;

$this->deleteDirectory($this->getServer()->getDataPath() . "/worlds/" .$this->games[$game]["Arena"]);
                $this->copymap($this->getDataFolder() . "/maps/TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/" . $this->games[$game]["Arena"]);

}


}
















public function Second(){

$this->SignUpdate();


foreach($this->games as $game => $value){

$Arena = $value["Arena"];
$level = $this->getServer()->getLevelByName($Arena);


if($this->getServer()->isLevelLoaded($Arena)){


$Status = $value["Status"];
$RedScore = $value["RedScore"];
$BlueScore = $value["BlueScore"];




if($this->games[$this->getGameByLevel($Arena)]["Status"] == "INGAME"){





foreach($this->getServer()->getLevelByName($Arena)->getPlayers() as $player){




if($this->getTeam($player) == "Red"){

$player->sendPopup("§cRed: ". $RedScore."    §bBlue: ".$BlueScore);
}


 if($this->getTeam($player) == "Blue"){
$player->sendPopup("§bBlue: ". $BlueScore."    §cRed: ".$RedScore);
}



}

}elseif($Status == "JOINABLE"){

foreach($this->getServer()->getLevelByName($Arena)->getPlayers() as $player){
$player->sendPopup("§eWaiting for players...   §d".count($this->getServer()->getLevelByName($Arena)->getPlayers())." / ".$this->MAX);

}

// SEND PLAYERS COUNTDOWN START MESSAGES

}elseif(is_numeric($Status) && $Status > 5){
$this->games[$game]["Status"] -= 1;

if($Status < 16){
 $c = "§c"; 
}else{
$c = "§a";
}


 foreach($level->getPlayers() as $player){

$player->sendPopup("§7Starts in ".$c.gmdate("i.s", $Status - 5));

}


}elseif(is_numeric($Status) && $Status < 6){

if(count($this->getServer()->getLevelByName($Arena)->getPlayers()) < $this->MAX && $Status == 5) $this->StartGame($Arena);



if($Status == 1) $this->games[$game]["Status"] = "zero"; else $this->games[$game]["Status"] -= 1;

 foreach($level->getPlayers() as $player){
$player->sendPopup("§7Starts in §b".$Status." §7Seconds");


}



}elseif($Status == "zero"){


$this->games[$game]["Status"] = "INGAME";
 foreach($level->getPlayers() as $player){
$player->sendPopup("§dGame has started!");
$player->sendMessage($this->PREFIX."Game has started! Happy fighting!");




} }

}}}






// thanks to @CraftYourBukkit for the code below




public function ResetMap($levelname){

$this->deleteDirectory($this->getServer()->getDataPath() . "/worlds/" . $levelname);

                $this->copymap($this->getDataFolder() . "/maps/" . "TW-BACKUP", $this->getServer()->getDataPath() . "/worlds/" . $levelname);

}





public function copymap($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->copymap($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteDirectory($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        $this->deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }






}
