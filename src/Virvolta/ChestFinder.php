<?php

namespace Virvolta;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;

class ChestFinder extends PluginBase implements Listener
{
    public $config;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this , $this);

        @mkdir($this->getDataFolder());

        if(!file_exists($this->getDataFolder()."config.yml")){

            $this->saveResource('config.yml');

        }

        $this->config = new Config($this->getDataFolder().'config.yml', Config::YAML);

    }

    public function onHeld(PlayerItemHeldEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $id = $this->config->get("id");
        $ditem = Item::fromString($id);

        if ($item->getId() === $ditem->getId() and $ditem->getDamage()) {

            new ChestFinderTask($this,$player);

        }

    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($item->getId() === $this->config->get("id") &&
            $item->getDamage() === $this->config->get("data") ) {

            new ChestFinderTask($this,$player);

        }

    }

}

class ChestFinderTask extends Task
{
    private $player;
    private $plugin;

    public function __construct(ChestFinder $plugin,Player $player)
    {
        $plugin->getScheduler()->scheduleRepeatingTask($this, 20*$plugin->config->get("repeat"));
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun(int $tick)
    {

        if($this->player->isOnline()) {

            $item = $this->player->getInventory()->getItemInHand();

            if ($item->getId() === $this->plugin->config->get("id") &&
                $item->getDamage() === $this->plugin->config->get("data")) {

                $n = 0;

                foreach($this->player->getLevel()->getTiles() as $t) {

                    if ($t instanceof Chest) {

                        if ($this->player->distance($t) <= $this->plugin->config->get("radius")) {

                            $n++;

                        }

                    }

                }

                if($n === 0){

                    $this->player->sendPopup($this->plugin->config->get("msg-chest-null"));

                } else {

                    $chest = str_replace("%",$n,$this->plugin->config->get("msg-chest"));

                    $this->player->sendPopup($chest);

                }

            } else {

                $this->plugin->getScheduler()->cancelTask($this->getTaskId());

            }

        } else {

            $this->plugin->getScheduler()->cancelTask($this->getTaskId());

        }

    }

}
