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
namespace TelegramBot\sender;

use pocketmine\command\ConsoleCommandSender as PMConsoleCommandSender;
use pocketmine\lang\TextContainer;
use pocketmine\utils\TextFormat;

class ConsoleCommandSender extends PMConsoleCommandSender{

	protected $line = "";

	public function sendMessage($message){
		parent::sendMessage($message);
		if($message instanceof TextContainer){
			$message = $this->getServer()->getLanguage()->translate($message);
		}else{
			$message = $this->getServer()->getLanguage()->translateString($message);
		}
		$message = TextFormat::clean($message);
		$this->line .= ($this->line === "" ? "" : "\n") . $message;
	}

	public function getLine() : string{
		$line = $this->line;
		return $line;
	}

	public function cleanLine() : void{
		$this->line = "";
	}
}