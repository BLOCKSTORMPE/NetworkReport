<?php
namespace RC;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\{Player, Server};
use pocketmine\utils\Config;
use pocketmine\utils\Scheduler;
class Main extends PluginBase implements Listener{
	public $prefix = "§7[§3System§7]";#
	public $reportPrefix = "§7[§b§lReportSystem§r§7]";
	function randomString($length = 5) {
    $str = "";
    $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
 }

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		#$this->getServer()->schedule
		}
		public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
			if(strtolower($cmd) == "notify"){
				if($sender instanceof Player){
					if($sender->hasPermission("system.notify")){
						$tcfg = new Config("/UniverseMC/Notify/".$sender->getName().".yml", Config::YAML);
						if($tcfg->get("Notify") == true){
							$tcfg->set("Notify", false);
$tcfg->save();
							$sender->sendMessage($this->prefix ." Benachrichtigungen wurden deaktiviert");

						}elseif($tcfg->get("Notify") == false){
							$tcfg->set("Notify", true);
							$tcfg->save();
							$sender->sendMessage($this->prefix ."Benachrichtigungen wurden aktiviert");
						}
					}
				}
			}

				if(strtolower($cmd == "report")){
					if($sender instanceof Player){
					if(empty($args[0]) || empty($args[1])){
						$sender->sendMessage($this->reportPrefix ."§cSyntax Fehler. Richtig: /report [Spieler] <Grund>");
					}else{
						if(file_exists("/UniverseMC/Spieler/".$args[0].".yml")){
							$tconfig = new Config("/UniverseMC/Jump/".$args[0].".yml", Config::YAML);
							if($tconfig->get("Status") == "Online"){
								if($args[1] == "Hacking" || $args[1] == "Teaming" || $args[1] == "Bugusing" || $args[1] == "Wortwahl" || $args[1] == "Werbung" || $args[1] == "Sonstiges"){
									$reports = 0;
									$rcfg = new Config("/UniverseMC/Reports/".self::randomString().".yml", Config::YAML);
									$rcfg->set("Reporter", $sender->getName());
									$rcfg->set("Reported", $args[0]);
									$rcfg->set("Grund", $args[1]);
									$rcfg->set("Status", "Offen");
									$rcfg->set("Staff", null);
									$rcfg->save();
									$sender->sendMessage($this->reportPrefix ."§aDein Report wird bald von einem Moderator bearbeitet.Bitte habe etwas geduld");
									foreach($this->getServer()->getOnlinePlayers() as $p){
										if(file_exists("/UniverseMC/Notify/".$p->getName().".yml")){
										$tcfg = new Config("/UniverseMC/Notify/".$p->getName().".yml", Config::YAML);
										if($tcfg->get("Notify") == true){
											$p->sendMessage($this->reportPrefix ."§c".$rcfg->get("Reported")." §7wurde wegen §5 ".$rcfg->get("Grund")."§cReportet");
										}
									}
								}
							}else{
								$sender->sendMessage($this->reportPrefix ."§cValide Gründe: Hacking, Teaming, Bugusing, Wortwahl, Werbung, Sonstiges");
							}
						}else{
							$sender->sendMessage($this->reportPrefix ."Dieser Spieler ist zur Zeit nicht online");
						}
					}else{
						$sender->sendMessage($this->reportPrefix ."§cDieser Spieler hat das Netzwerk noch nie betreten!");
					}
				}
			}
		}
		if(strtolower($cmd == "current")){
			if($sender instanceof Player){
				if($sender->hasPermission("universe.notify")){
					if(empty($args[0]) || empty($args[1])){
						$sender->sendMessage($this->reportPrefix ."§cSyntax Error: Fehlende Argumente§7[§4Error #C02A§7]");
						$sender->sendMessage($this->reportPrefix ."§b=-=-=- Report-Hilfemenu für §c/current §b=-=-=-");
						$sender->sendMessage($this->reportPrefix ."§c/report close §6- §aSchließe den Report, den du aktuell bearbeitest");
						$sender->sendMessage($this->reportPrefix ."§c/report update [ID] §6- §aUpdate den Status des aktuellen Reports§7[IDs: §e1 = Offen, 2 = Nicht nachprüfbar§7]");
						$sender->sendMessage($this->reportPrefix ."§b=-=-=- Report-Hilfemenu für §c/current §b=-=-=-");
					}else{
					$jumper1 = new Config("/UniverseMC/Notify/".$sender->getName().".yml", Config::YAML);
					if($jumper1->get("Report") != null){
						$reportid = $jumper1->get("Report");
						$reportconfig = new Config("/UniverseMC/Reports/".$reportid.".yml", Config::YAML);
						switch($args[0]){
							case "close":
								unlink("/UniverseMC/Reports/".$reportid.".yml");
								$sender->sendMessage($this->reportPrefix ."§cDer Report wurde geschlossen. Du wirst nun auf die Lobby zurückgesendet");
								$sender->transfer("blockstorm.tk", "19132");
							break;
							case "update":
								switch($args[1]){
									case "1":
										$reportconfig->set("Status", "Offen");
										$reportconfig->save();
										$sender->sendMessage($this->reportPrefix ."§cDer Status des aktuellen Reports wurde auf §aOFFEN §cgesetzt. Du kannst den Report mit §5/current close§c schließen");
									break;
									case "2":
										$reportconfig->set("Status", "Nicht nachprüfbar");
										$reportconfig->save();
										$sender->sendMessage($this->reportPrefix ."§cDer Status des aktuellen Reports wurde auf §eNICHT NACHPRÜFBAR §cgesetzt. Du kannst den Report mit §5/report close §5schließen");
									break;
								}
							break;
						break;
						}
					}else{
						$sender->sendMessage($this->reportPrefix ."§cDu bearbeitest aktuell keinen Report!");
					}
				}
			}
		}
	}
	if(strtolower($cmd == "rpurge")){
		if($sender instanceof Player){
			if($sender->hasPermission("universe.rpurge")){
				unlink("/UniverseMC/Reports/*.*");
				$sender->sendMessage($this->reportPrefix ."§cAlle Reports wurden gelöscht");
			}
		}
	}
		if(strtolower($cmd == "claim")){
			if($sender instanceof Player){
				if($sender->hasPermission("universe.notify")){
					if(file_exists("/UniverseMC/Reports/".$args[0].".yml")){
						$report = new Config("/UniverseMC/Reports/".$args[0].".yml", Config::YAML);
						$jumper = new Config("/UniverseMC/Notify/".$sender->getName().".yml", Config::YAML);
						if($report->get("Status") == "Offen"){
							$jumper->set("Report", $args[0]);
							$jumper->set("ReportModus", true);
							$jumper->save();
							$report->set("Staff", $sender->getName());
							$report->set("Status", "Checking");
							$report->save();

$sender->sendMessage($this->reportPrefix ."§cDu hast den Report §e".$args[0]." §cübernommen");
$config = new Config("/UniverseMC/Reports/".$args[0].".yml", Config::YAML);
							$sender->sendMessage($this->reportPrefix ."§b=-=-= §cReport Informationen für ".$args[0]." §b=-=-=");
							$sender->sendMessage($this->reportPrefix ."§cReporter: §5".$config->get("Reporter")."");
							$sender->sendMessage($this->reportPrefix ."§cReported: §5".$config->get("Reported")."");
							$sender->sendMessage($this->reportPrefix ."§cGrund: §5".$config->get("Grund")."");
							$sender->sendMessage($this->reportPrefix ."§cStatus: §5".$config->get("Status")."");
							$sender->sendMessage($this->reportPrefix ."§cStaff: §5".$config->get("Staff")."");
							$sender->sendMessage($this->reportPrefix ."§b=-=-= §cReport Informationen für ".$args[0]." §b=-=-=");
$commander = "jumpto :als";
$stringer = str_replace(":als", $report->get("Reported"), $commander);
							$this->getServer()->dispatchCommand($sender->getPlayer(), $stringer);
						}


					}else{
						$sender->sendMessage($this->reportPrefix ."§cDieser Report existiert nicht");
					}
				}
			}
		}
			if(strtolower($cmd == "reportinfo")){
				if($sender instanceof Player){
					if($sender->hasPermission("universe.notify")){
						if(file_exists("/UniverseMC/Reports/".$args[0].".yml")){
							$config = new Config("/UniverseMC/Reports/".$args[0].".yml", Config::YAML);
							$sender->sendMessage($this->reportPrefix ."§b=-=-= §cReport Informationen für ".$args[0]." §b=-=-=");
							$sender->sendMessage($this->reportPrefix ."§cReporter: §5".$config->get("Reporter")."");
							$sender->sendMessage($this->reportPrefix ."§cReported: §5".$config->get("Reported")."");
							$sender->sendMessage($this->reportPrefix ."§cGrund: §5".$config->get("Grund")."");
							$sender->sendMessage($this->reportPrefix ."§cStatus: §5".$config->get("Status")."");
							$sender->sendMessage($this->reportPrefix ."§cStaff: §5".$config->get("Staff")."");
							$sender->sendMessage($this->reportPrefix ."§b=-=-= §cReport Informationen für ".$args[0]." §b=-=-=");
						}else{
							$sender->sendMessage($this->reportPrefix ."§cDieser Report existiert nicht");
						}
					}
				}
			}
			if(strtolower($cmd == "reports")){
				if($sender instanceof Player){
					if($sender->hasPermission("universe.notify")){
						$files = scandir("/UniverseMC/Reports");

						foreach($files as $report){
							$filename = str_replace(".yml", "", $report);
								if($filename != "." && $filename != ".."){
									$configr = new Config("/UniverseMC/Reports/".$filename.".yml", Config::YAML);
									$reason = $configr->get("Grund");
									$reported = $configr->get("Reported");


									$sender->sendMessage($this->reportPrefix ."§9- §e".$reported." §7| §e".$reason." §7| §c".$filename." §9-");
								}
						}
				}
			}
		}
	return true;
}
public function onPacketR(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		if($event instanceof ModalFormResponsePacket){
			if($packet->formId == "7"){
				$reportc = new Config("/UniverseMC/Reports/".$data.".yml", Config::YAML);
				$reportc->set("Staff", $name);
				$reportc->set("Status", "Checking");
				$reportc->save();
				$staffconfig = new Config("/UniverseMC/Notify/".$name.".yml", Config::YAML);
				$staffconfig->set("Report-Modus", true);
				$staffconfig->set("Spectating", $reportc->get("Reported"));
				$staffconfig->set("ReportID", $data);
				$staffconfig->save();
			}
		}
	}
}
