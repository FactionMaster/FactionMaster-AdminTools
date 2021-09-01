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

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\Button\Collection\AdminToolsMain as CollectionAdminToolsMain;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;

class AdminToolsMain implements Route {

    const SLUG = "adminToolsMain";

    public $PermissionNeed = [
        [
            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
            PermissionConstant::DELETE_FACTION_PERMISSION
        ],
        [
            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
            PermissionConstant::DELETE_INVITATION_PERMISSION
        ]
    ];

    /** @var UserEntity */
    private $UserEntity;
    /** @var Collection */
    private $Collection;

    public function getSlug(): string
    {
        return self::SLUG;
    }
    
    public function __invoke(Player $Player, UserEntity $User, array $UserPermissions, ?array $params = null){
        $this->UserEntity = $User;
        $message = '';
        if (isset($params[0])) $message = $params[0];

        $this->Collection = CollectionFactory::get(CollectionAdminToolsMain::SLUG)->init($Player, $User);
        $menu = $this->mainMenu($message);
        $Player->sendForm($menu);
    }

    public function call() : callable{
        $Collection = $this->Collection;
        return function (Player $Player, $data) use ($Collection) {
            if ($data === null) return;
            $Collection->process($data, $Player);
        };
    }

    private function mainMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = $this->Collection->generateButtons($menu, $this->UserEntity->name);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MAIN_ADMIN_TOOLS_PANEL_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }
}