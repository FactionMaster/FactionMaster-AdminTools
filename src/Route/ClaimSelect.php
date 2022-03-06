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
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouteBase;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;
use function call_user_func;
use function count;
use function is_callable;

class ClaimSelect extends RouteBase {
	const SLUG = "selectClaim";

	/** @var bool */
	private $menuActive = false;
	private $options = [];
	private $optionsBis = [];

	public function getSlug(): string {
		return self::SLUG;
	}

	public function getPermissions(): array {
		return [
			[
				Utils::POCKETMINE_PERMISSIONS_CONSTANT,
				PermissionConstant::DELETE_CLAIM_PERMISSION
			],
			[
				Utils::POCKETMINE_PERMISSIONS_CONSTANT,
				PermissionConstant::TP_CLAIM_PERMISSION
			]
		];
	}

	public function getBackRoute(): ?Route {
		return $this->getParams()[2];
	}

	public function getTargetFaction(): string {
		return $this->getParams()[0] ?? "";
	}

	public function getCallable(): callable {
		return $this->getParams()[1];
	}

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
		$player->sendForm($this->getForm());
	}

	public function call() : callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			if (!$this->menuActive) {
				Utils::processMenu($this->getBackRoute(), $player);
				return;
			}
			call_user_func($this->getCallable(), $this->getTargetFaction(), $this->optionsBis[$data[0]]->id);
		};
	}

	private function getForm(): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_SELECT_CLAIM_TITLE"));
		$this->options = [];
		$this->optionsBis = [];
		foreach (MainAPI::getClaimsFaction($this->getTargetFaction()) as $claim) {
			$this->optionsBis[] = $claim;
			$this->options[] = $claim->toString();
		}
		if (count($this->options) != 0) {
			$menu->addDropdown(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_SELECT_CLAIM_INFORMATION"), $this->options);
			$this->menuActive = true;
		} else {
			$menu->addLabel(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_SELECT_CLAIM_ERROR"));
		}
		return $menu;
	}
}