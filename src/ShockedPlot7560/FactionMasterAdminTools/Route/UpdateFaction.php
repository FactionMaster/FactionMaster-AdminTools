<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster and is an extension
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
*/

namespace ShockedPlot7560\FactionMasterAdminTools\Route;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;
use function call_user_func;
use function is_callable;
use function is_string;

class UpdateFaction implements Route {
	const SLUG = "updateFaction";

	public $PermissionNeed = [
		[
			Utils::POCKETMINE_PERMISSIONS_CONSTANT,
			PermissionConstant::UPDATE_FACTION_PERMISSION
		]
	];
	public $callable;
	public $backMenu;
	/** @var RewardInterface[] */
	private $optionsBis = [];
	private $factionName;

	public function getSlug(): string {
		return self::SLUG;
	}

	/**
	 * @param array|null $params Give to first item the message to print if wanted
	 */
	public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null) {
		$this->UserEntity = $User;
		if (isset($params[0]) && is_string($params[0])) {
			$factionName = $params[0];
		}
		if (isset($params[1]) && is_callable($params[1])) {
			$this->callable = $params[1];
		}
		if (isset($params[2]) && is_string($params[2])) {
			$this->backMenu = $params[2];
		}
		if (isset($params[0]) && $params[0] == "") {
			return Utils::processMenu(RouterFactory::get($this->backMenu), $player);
		}
		$menu = $this->createSelectMenu();
		$this->factionName = $factionName;
		$player->sendForm($menu);
	}

	public function call() : callable {
		$backMenu = $this->backMenu;
		$callable = $this->callable;
		return function (Player $Player, $data) use ($backMenu, $callable) {
			if ($data === null || !isset($backMenu) || !isset($callable)) {
				return;
			}
			call_user_func($callable, $this->factionName, $this->optionsBis[$data[0]], $data[1]);
		};
	}

	private function createSelectMenu(): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_TITLE"));
		$options = [];
		$this->optionsBis = [];
		foreach (RewardFactory::getAll() as $type => $reward) {
			$options[] = Utils::getText($this->UserEntity->name, $reward->getName($this->UserEntity->name));
			$this->optionsBis[] = $reward;
		}
		$menu->addDropdown(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_INFORMATION"), $options);
		$menu->addInput(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_INPUT_TITLE"), Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_INPUT_PLACEHOLDER"));
		return $menu;
	}
}