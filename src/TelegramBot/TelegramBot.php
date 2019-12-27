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
namespace TelegramBot;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\MainLogger;
use TelegramBot\sender\ConsoleCommandSender;
use TelegramBot\task\CheckLimitTask;
use TelegramBot\task\CheckMessageTask;

class TelegramBot extends PluginBase{

	/** @var ConsoleCommandSender */
	protected $consoleCommandSender;

	private static $instance = null;

	public function onLoad(){
		self::$instance = $this;
	}

	public function onEnable(){
		$this->saveResource("config.yml");

		if($this->getConfig()->getNested("bot-token", "") === ""){
			$this->getLogger()->error("config.yml에서 \"bot-token\"을 설정해주세요.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		Queue::$lastMessage = $this->getConfig()->getNested("last-message", "");

		Queue::$botToken = $this->getConfig()->getNested("bot-token", "");

		$this->consoleCommandSender = new ConsoleCommandSender();
		$this->getScheduler()->scheduleDelayedRepeatingTask(new CheckMessageTask(), 20 * 5, 20 * 5);
		//$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function onDisable(){
		$this->getConfig()->setNested("last-message", Queue::$lastMessage);
		$this->getConfig()->save();
	}

	public static function getInstance() : TelegramBot{
		return self::$instance;
	}

	public function dispatchCommand(string $command, string $args = "") : string{
		$this->getServer()->dispatchCommand($this->consoleCommandSender, $command . $args);
		$line = $this->consoleCommandSender->getLine();
		if(trim($line) === ""){
			$result = new \ReflectionClass(MainLogger::class);
			$p = $result->getProperty("logStream");
			$p->setAccessible(true);

			/** @var \Threaded $v */
			$v = $p->getValue(MainLogger::getLogger());
			$line = $v->shift();
			if($line === null){
				$line = "";
			}
		}
		return $line;
	}

	public function getSender() : ConsoleCommandSender{
		return $this->consoleCommandSender;
	}
}