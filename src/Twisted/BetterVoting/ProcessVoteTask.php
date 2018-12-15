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
		$this->setResult(Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . $this->username));
	}

	public function onCompletion(Server $server): void{
		$result = $this->getResult();
		$player = $server->getPlayer($this->username);
		if($player == null) return;
		if($result == "Error: server key not found"){
			$player->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
			return;
		}
		if(!(bool)$result){
			$player->sendMessage(TextFormat::RED . "You have not voted yet");
			return;
		}
		/** @var BetterVoting $main */
		$main = $server->getPluginManager()->getPlugin("BetterVoting");
		//Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . $this->username);
		$main->claimVote($player);
	}
}