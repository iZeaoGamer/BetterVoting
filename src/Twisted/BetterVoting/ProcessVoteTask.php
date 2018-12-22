<?php
declare(strict_types=1);

namespace Twisted\BetterVoting;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class ProcessVoteTask extends AsyncTask{

	/** @var string $apiKey */
	private $apiKey;
	/** @var string $username */
	private $username;

	public function __construct(string $apiKey, string $username){
		$this->apiKey = $apiKey;
		$this->username = $username;
	}

	public function onRun(): void{
		$result = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . $this->username);
		if($result === "1") Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . $this->username);
		$this->setResult($result);
	}

	public function onCompletion(Server $server): void{
		$result = $this->getResult();
		$player = $server->getPlayer($this->username);
		if($player === null) return;
		if($result === "Error: server key not found"){
			$player->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
			return;
		}
		if($result === "0"){
			$player->sendMessage(TextFormat::RED . "§cYou have not voted yet\n§bVote using this link: §3http://zpevote.ml §bfor §3cool rewards!");
			return;
		}
		if($result === "4"){
			$player->sendMessage(TextFormat::RED . "§cYou have already voted today");
			return;
		}
		/** @var BetterVoting $main */
		$main = $server->getPluginManager()->getPlugin("BetterVoting");
		$main->claimVote($player);
	}
}
