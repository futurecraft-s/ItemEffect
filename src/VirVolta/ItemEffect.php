<?php

#FUCK YUMIKO

namespace VirVolta;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

class ItemEffect extends PluginBase implements Listener
{
    private $config;
    private $interact = [];

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        @mkdir($this->getDataFolder());

        if(!file_exists($this->getDataFolder()."config.yml")){

            $this->saveResource('config.yml');
        }

        $this->config = new Config($this->getDataFolder().'config.yml', Config::YAML);
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        foreach ($this->config->getAll() as $id => $array) {

            if ($item->getId() === $id && $item->getDamage() === $array["damage"]) {

                if ($this->onCountDown($player,$array["countdown"])) {

                    if ($array["consume"] == true) {

                        $player->getInventory()->removeItem(Item::get($id,$array["damage"],1));

                    }

                    if ($array["message"] !== " ") {

                        $player->sendMessage($array["message"]);

                    }

                    $effects = $array["effect"];

                    foreach ($effects as $effectid => $arrayeffect) {

                        $eff = new EffectInstance(
                            Effect::getEffect($effectid) ,
                            (int)$arrayeffect["durability"] * 20 ,
                            (int)$arrayeffect["amplifier"],
                            (bool)$arrayeffect["visible"]
                        );

                        $player->addEffect($eff);

                    }

                    $event->setCancelled();

                }
                break;

            }

        }

    }

    public function onCountDown(Player $player , int $countdown) : bool
    {
        if ($countdown <= 0.5) {

            $countdown = 0.5;
        }

        if(isset($this->interact[strtolower($player->getName())]) &&
            time() - $this->interact[strtolower($player->getName())]  < $countdown) {

            return false;

        } else {

            $this->interact[strtolower($player->getName())] = time();

        }

        return true;

    }

}
