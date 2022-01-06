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

namespace ShockedPlot7560\FactionMasterAdminTools\Button\Collection;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Button\Back;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMasterAdminTools\Button\DeleteClaim;
use ShockedPlot7560\FactionMasterAdminTools\Button\DeleteFaction;
use ShockedPlot7560\FactionMasterAdminTools\Button\DeleteHome;
use ShockedPlot7560\FactionMasterAdminTools\Button\DeleteInvitation;
use ShockedPlot7560\FactionMasterAdminTools\Button\TpClaim;
use ShockedPlot7560\FactionMasterAdminTools\Button\TpHome;
use ShockedPlot7560\FactionMasterAdminTools\Button\UpdateFaction;

class AdminToolsMain extends Collection {
	const SLUG = "adminToolsMain";

	public function __construct() {
		parent::__construct(self::SLUG);
		$this->registerCallable(self::SLUG, function(Player $player, UserEntity $user) {
			$this->register(new DeleteFaction());
			$this->register(new DeleteInvitation());
			$this->register(new DeleteHome());
			$this->register(new TpHome());
			$this->register(new DeleteClaim());
			$this->register(new TpClaim());
			$this->register(new UpdateFaction());
			$this->register(new Back(RouterFactory::get(AdminToolsMain::SLUG)->getBackRoute()));
		});
	}
}