<?php

namespace NoCmd;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\server\CommandEvent;
use pocketmine\utils\Config;

class NoCmd extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!file_exists($this->getDataFolder())) {
            @mkdir($this->getDataFolder(), 0744, true);
        }
        $this->list = new Config($this->getDataFolder() . "list.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "nocmd":
                if (!isset($args[0])) {
                    return false;
                }
                switch ($args[0]) {
                    case "add":
                        if (!$sender->isop()) {
                            $sender->sendmessage("§cこのコマンドを実行する権限がありません");
                            break;
                        }
                        if (!isset($args[1]) or !isset($args[2])) {
                            $sender->sendmessage("[NoCmd] §c/nocmd add 名前 理由");
                            break;
                        }
                        if ($this->list->exists($args[1])) {
                            $sender->sendmessage("[NoCmd] §c{$args[1]}は既にコマンドの使用を制限されています");
                            break;
                        }
                        $this->getServer()->broadcastMessage("[NoCmd] §e{$args[1]}のコマンドの使用を制限しました");
                        $this->getServer()->broadcastMessage("[NoCmd] §e理由 : {$args[2]}");
                        $this->list->set($args[1], $args[2]);
                        $this->list->save();
                        break;
                    case "remove":
                        if (!isset($args[1])) {
                            $sender->sendmessage("[NoCmd] §c/nocmd remove 名前");
                            break;
                        }
                        if (!$this->list->exists($args[1])) {
                            $sender->sendmessage("[NoCmd] §c{$args[1]}はコマンドを制限されていません");
                            break;
                        }
                        $sender->sendmessage("[NoCmd] §e{$args[1]}のコマンドの使用の制限を解除しました");
                        $this->list->remove($args[1]);
                        $this->list->save();
                        break;
                    case "list":
                        $sender->sendMessage("§aコマンド使用制限リスト");
                        foreach ($this->list->getAll() as $key => $value) {
                            $sender->sendMessage("{$key}   理由 : {$this->list->get($key)}");
                        }
                        break;
                    default:
                        return false;
                }
                break;
        }
        return true;
    }

    public function onCmd(CommandEvent $event)
    {
        if ($this->list->exists($event->getSender()->getName())) {
            $event->getSender()->sendmessage("§cあなたはコマンドの使用を制限されています");
            $event->setCancelled();
        }
    }

}
