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
use TelegramBot\TelegramBot;

class SendMessageAsyncTask extends AsyncTask{

	private $message;

	private $senderId;

	private $group_id;

	private $messageId;

	private $token;

	public function __construct(string $message, int $senderId, int $group_id, int $messageId = -1, string $token = ""){
		$this->message = $message;
		$this->senderId = $senderId;
		$this->group_id = $group_id;
		$this->messageId = $messageId;
		$this->token = $token;
	}

	public function onRun() : void{
		$url = "https://api.telegram.org/$this->token/sendMessage?";
		$id = $this->senderId;

		$header = ["chat_id" => $id, "text" => $this->message];

		if($this->messageId !== -1){
			$header["reply_to_message_id"] = $this->messageId;
		}

		$result = Internet::postURL($url, $header);

		$data = json_decode($result, true);

		if($data["ok"] === true){
			$this->setResult(["result" => $data["result"] ["text"]]);
		}else{
			$this->setResult(null);
		}
	}

	public function onCompletion(Server $server){
		if($this->getResult() !== null){
			TelegramBot::getInstance()->getLogger()->info("Message sent successfully: " . $this->getResult()["result"]);
		}else{
			TelegramBot::getInstance()->getLogger()->error("Message sent faild: " . $this->message);
		}
		//TelegramBot::getInstance()->getLogger()->info("SENDMESSAGE SUCCESSFULLY");
	}
}