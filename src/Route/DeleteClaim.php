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
 * This file is part of FactionMaster
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

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouteBase;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\FactionMasterAdminTools;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;

class DeleteClaim extends RouteBase {
	const SLUG = "deleteClaimPanel";

	public function getSlug(): string {
		return self::SLUG;
	}

	public function getPermissions(): array {
		return [
			[
				Utils::POCKETMINE_PERMISSIONS_CONSTANT,
				PermissionConstant::DELETE_CLAIM_PERMISSION
			]
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(AdminToolsMain::SLUG);
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$message = $params[0] ?? "";
		$player->sendForm($this->getForm($message));
	}

	public function call() : callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			$userEntity = $this->getUserEntity();
			Utils::processMenu(RouterFactory::get(ClaimSelect::SLUG), $player, [
				$data[1],
				function (string $factionName, int $factionClaim) use ($player, $userEntity) {
					FactionMasterAdminTools::getInstance()->getServer()->getAsyncPool()->submitTask(
						new DatabaseTask(
							"DELETE FROM " . ClaimTable::TABLE_NAME . " WHERE id = :id",
							["id" => $factionClaim],
							function () use ($factionClaim, $factionName, $player, $userEntity) {
								foreach (MainAPI::$claim[$factionName] as $key => $claim) {
									if ($claim->getId() == $factionClaim) {
										unset(MainAPI::$claim[$factionName][$key]);
									}
								}
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($factionName, $factionClaim) {
										foreach (MainAPI::getClaimsFaction($factionName) as $claim) {
											if ($claim->getId() == $factionClaim) {
												return false;
											}
										}
										return true;
									},
									function () use ($player, $userEntity) {
										Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($userEntity->getName(), "ADMIN_TOOLS_DELETE_CLAIM_SUCCESS")] );
									},
									function () use ($player, $userEntity) {
										Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($userEntity->getName(), "ERROR")]);
									}
								));
							}
						)
					);
				},
				$this->getBackRoute()
			]);
		};
	}

	private function getForm(string $message = "") : CustomForm {
		$menu = new CustomForm($this->call());
		$menu->addLabel($message);
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_INSTRUCTION"), Utils::getText($this->getUserEntity()->getName(), "ADMIN_TOOLS_DELETE_CLAIM_PLACEHOLDER"));
		return $menu;
	}
}