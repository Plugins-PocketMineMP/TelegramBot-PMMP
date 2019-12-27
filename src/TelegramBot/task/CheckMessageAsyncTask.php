<?php

/*
 *       _       _        ___ _____ _  ___
 *   __ _| |_   _(_)_ __  / _ \___ // |/ _ \
 * / _` | \ \ / / | '_ \| | | ||_ \| | (_) |
 * | (_| | |\ V /| | | | | |_| |__) | |\__, |
 *  \__,_|_| \_/ |_|_| |_|\___/____/|_|  /_/
 *
 * Copyright (C) 2019 alvin0319
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);
namespace TelegramBot\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use TelegramBot\Queue;
use TelegramBot\TelegramBot;

class CheckMessageAsyncTask extends AsyncTask{

	protected $sendReply = false;

	protected $token;

	public function __construct(string $token, bool $sendReply = true){
		$this->token = $token;
		$this->sendReply = $sendReply;
	}

	public function onRun() : void{


		$data = json_decode(Internet::getURL("https://api.telegram.org/$this->token/getUpdates", 10, [], $err), true);

		if((bool) $data["ok"]){
			$lastMessage = $data["result"] [count($data["result"]) - 1] ["message"] ["text"] ?? "NULL";
			$senderId = $data["result"] [count($data["result"]) - 1] ["message"] ["chat"] ["id"];
			$messageId = $data["result"] [count($data["result"]) - 1] ["message"] ["message_id"];

			if(trim(Queue::$lastMessage) !== trim($lastMessage)){
				Queue::$lastMessage = $lastMessage;
				if(strpos($lastMessage, "/execute") !== false){
					$commands = explode(" ", $lastMessage);
					$command = "";
					$args = "";
					foreach($commands as $key => $c){
						if($key === 1){
							$command = $c;
						}elseif($key >= 2){
							$args .= (substr($args, -1) !== " " ? " " : "") . $c;
						}
					}
					$c = ["command" => $command, "args" => $args, "senderId" => $senderId];
					if($this->sendReply){
						$c["messageId"] = $messageId;
					}else{
						$c["messageId"] = -1;
					}

					$this->setResult($c);
				}
			}
			return;
		}
		$this->setResult(null);
	}

	public function onCompletion(Server $server){
		if($this->getResult() !== null){
			$server->getAsyncPool()->submitTask(new SendMessageAsyncTask(TelegramBot::getInstance()->dispatchCommand($this->getResult()["command"], $this->getResult()["args"]), (int) $this->getResult()["senderId"], 0, (int) $this->getResult()["messageId"], Queue::$botToken));
			TelegramBot::getInstance()->getSender()->cleanLine();
		}
	}
}