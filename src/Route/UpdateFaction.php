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

use InvalidArgumentException;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouteBase;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;
use function call_user_func;
use function count;
use function is_callable;

class UpdateFaction extends RouteBase {
	const SLUG = "updateFaction";

	/** @var RewardInterface[] */
	private $optionsBis = [];

	public function getSlug(): string {
		return self::SLUG;
	}

	public function getPermissions(): array {
		return[
			[
				Utils::POCKETMINE_PERMISSIONS_CONSTANT,
				PermissionConstant::UPDATE_FACTION_PERMISSION
			]
		];
	}

	public function getBackRoute(): ?Route {
		return $this->getParams()[2];
	}

	public function getCallable(): callable {
		return $this->getParams()[1];
	}

	public function getTargetFaction(): string {
		return $this->getParams()[0];
	}

	/**
	 * @param array|null $params Give to first item the message to print if wanted
	 */
	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);
		if (count($params) < 3) {
			throw new InvalidArgumentException("params must have a size of 3");
		}
		if (!is_callable($params[1])) {
			throw new InvalidArgumentException("Second parameter of params must be a callable");
		}
		if (!$params[2] instanceof Route) {
			throw new InvalidArgumentException("Third parameter of params must be an instance of Route");
		}
		if ($this->getTargetFaction() === "") {
			Utils::processMenu($this->getBackRoute(), $player);
			return;
		}
		if (!MainAPI::getFaction($this->getTargetFaction()) instanceof FactionEntity) {
			Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "FACTION_DONT_EXIST")]);
			return;
		}
		$player->sendForm($this->getForm());
	}

	public function call() : callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			call_user_func($this->getCallable(), $this->getTargetFaction(), $this->optionsBis[$data[0]], $data[1]);
		};
	}

	private function getForm(): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_UPDATE_FACTION_TITLE"));
		$options = [];
		$this->optionsBis = [];
		foreach (RewardFactory::getAll() as $reward) {
			$options[] = Utils::getText($this->getUserEntity()->getName(), $reward->getName($this->getUserEntity()->getName()));
			$this->optionsBis[] = $reward;
		}
		$menu->addDropdown(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_UPDATE_FACTION_INFORMATION"), $options);
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_UPDATE_FACTION_INPUT_TITLE"), Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_UPDATE_FACTION_INPUT_PLACEHOLDER"));
		return $menu;
	}
}