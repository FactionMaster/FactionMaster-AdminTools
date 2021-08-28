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
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;

class HomeSelect implements Route {

    const SLUG = "selectHome";

    public $PermissionNeed = [
        [
            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
            PermissionConstant::DELETE_HOME_PERMISSION
        ]
    ];
    public $callable;
    public $backMenu;
    /** @var bool */
    private $menuActive = false;
    private $options = [];
    private $optionsBis = [];
    private $factionName;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null) {
        $this->UserEntity = $User;
        if (isset($params[0]) && \is_string($params[0])) $factionName = $params[0];
        if (isset($params[1]) && is_callable($params[1])) $this->callable = $params[1];
        if (isset($params[2]) && \is_string($params[2])) $this->backMenu = $params[2];
        if (isset($params[0]) && $params[0] == "") return Utils::processMenu(RouterFactory::get($this->backMenu), $player);
        $menu = $this->createSelectMenu($factionName);
        $this->factionName = $factionName;
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        $callable = $this->callable;
        return function (Player $Player, $data) use ($backMenu, $callable) {
            if ($data === null || !isset($backMenu) || !isset($callable)) return;
            if (!$this->menuActive || $data[0] === "") return Utils::processMenu(RouterFactory::get($backMenu), $Player);
            call_user_func($callable, $this->factionName, $this->optionsBis[$data[0]]);
        };
    }

    private function createSelectMenu(string $factionName): CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_SELECT_HOME_TITLE"));
        $this->options = [];
        $this->optionsBis = [];
        foreach (MainAPI::getFactionHomes($factionName) as $name => $home) {
            $this->options[] = $name . "(" .Utils::homeToString($home["x"], $home["y"], $home["z"], $home["world"]) . ")";
            $this->optionsBis[] = $name;
        }
        if (count($this->options) != 0) {
            $menu->addDropdown(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_DELETE_HOME_PLACEHOLDER"), $this->options);
            $this->menuActive = true;
        }else{
            $menu->addLabel(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_SELECT_HOME_ERROR"));
        }
        return $menu;
    }
}