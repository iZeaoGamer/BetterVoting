<?php
declare(strict_types=1);

namespace Twisted\BetterVoting;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class GetTopVotersTask extends AsyncTask{

	/** @var string $requester */
	private $requester;
	/** @var string $apiKey */
	private $apiKey;
	/** @var int $limit */
	private $limit;

	public function __construct(string $requester, string $apiKey, int $limit){
		$this->requester = $requester;
		$this->apiKey = $apiKey;
		$this->limit = $limit;
	}

	public function onRun(){
		$this->setResult(Internet::getURL("https://minecraftpocket-servers.com/api/?object=servers&element=voters&key=" . $this->apiKey . "&month=current&format=json&limit=" . $this->limit));
	}

	public function onCompletion(Server $server){
		$result = $this->getResult();
		$requester = $server->getPlayer($this->requester);
		if($requester === null) return;
		if($result === "Error: server key not found"){
			$requester->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
			return;
		}
		/** @var BetterVoting $plugin */
		$plugin = $server->getPluginManager()->getPlugin("BetterVoting");
		$topvotes = $plugin->getConfig()->get("top-votes");
		$votes = json_decode($result, true)["voters"];
		$requester->sendMessage(str_replace("&", "§", isset($topvotes["title"]) ? $topvotes["title"] : "&aTop Votes This Month"));
		$i = 1;
		foreach($votes as $vote){
			$requester->sendMessage(str_replace([
				"&",
				"{number}",
				"{username}",
				"{votes}"
			], [
				"§",
				$i,
				$vote["nickname"],
				$vote["votes"]
			], isset($topvotes["format"]) ? $topvotes["format"] : "&6{number}. &b{username}: &e{votes}"));
			$i++;
		}
	}
}