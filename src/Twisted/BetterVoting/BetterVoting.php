<?php
declare(strict_types=1);

namespace Twisted\BetterVoting;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class BetterVoting extends PluginBase{

	/** @var null|string $apiKey */
	private $apiKey = null;
	/** @var array $data */
	private $data = [];

	public function onEnable(): void{
		$config = $this->getConfig();
		if(empty($config->get("api-key"))) $this->getLogger()->error("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
		else $this->apiKey = $config->get("api-key");
		if(!is_array($config->get("claim"))) $this->getLogger()->error("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
		else $this->data = $config->get("claim");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if(empty($args[0])){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED . "Use '/vote reload', or use command in game");
				return false;
			}
			if($this->apiKey == null){
				$sender->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
				return false;
			}
			$this->getServer()->getAsyncPool()->submitTask(new ProccessVoteTask($this->apiKey, $sender->getName()));
			return true;
		}
		switch($args[0]){
			case "reload":
				if(!$sender->hasPermission("bettervoting.reload")){
					$sender->sendMessage(TextFormat::RED . "You do not have permission to use this command");
					break;
				}
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
				if(empty($config->get("api-key"))){
					$this->getLogger()->error("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
					$sender->sendMessage("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
				}else $this->apiKey = $config->get("api-key");
				if(!is_array($config->get("claim"))){
					$this->getLogger()->error("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
					$sender->sendMessage("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
				}else $this->data = $config->get("claim");
				$sender->sendMessage(TextFormat::GREEN . "Configuration successfully reloaded");
				break;
		}
		return true;
	}

	public function translateMessage(string $message, Player $player): string{
		return str_replace([
			"{real-name}",
			"{display-name}",
			"&"
		], [
			$player->getName(),
			$player->getDisplayName(),
			"§"
		], $message);
	}

	/**
	 * @return Item[]
	 */
	public function getItemRewards(): array{
		$items = $this->data["items"];
		if(!isset($items) || !is_array($items)){
			$this->getLogger()->error("Please give a valid item rewards [claim.items] array in " . $this->getDataFolder() . "config.yml (Delete to reset)");
			return [];
		}
		$rewards = [];
		foreach($items as $item){
			$info = explode(":", $item);
			if(empty($info[3])) break;
			$const = Item::class . "::" . strtoupper($info[0]);
			if(defined($const)) $reward = Item::get(constant($const), (int)$info[1], (int)$info[2]);
			if(strtolower($info[3]) !== "default") $reward->setCustomName($info[3]);
			$enchants = [];
			for($i = 0; $i < count($info); $i++){
				if($i > 3){
					$level = isset($info[$i + 1]) ? (int)$info[$i + 1] : 1;
					$enchants[strtolower($info[$i])] = $level;
					$i++;
				}
			}
			foreach($enchants as $enchant => $level){
				$const = Enchantment::class . "::" . strtoupper($enchant);
				if(defined($const)) $reward->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(constant($const)), $level));
			}
			$rewards[] = $reward;
		}
		return $rewards;
	}

	public function claimVote(Player $player): void{
		$data = $this->data;
		if(isset($data["broadcast"])) $player->getServer()->broadcastMessage($this->translateMessage($data["broadcast"], $player));
		if(isset($data["message"])) $player->sendMessage($this->translateMessage($data["message"], $player));
		foreach($this->getItemRewards() as $reward){
			if($player->getInventory()->canAddItem($reward)) $player->getInventory()->addItem($reward);
			else $player->getLevel()->dropItem($player, $reward);
		}
	}
}