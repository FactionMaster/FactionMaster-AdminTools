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

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionHomeDeleteEvent;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\PermissionConstant;

class UpdateFactionSelect implements Route {

    const SLUG = "updateFactionSelectPanel";

    public $PermissionNeed = [
        [
            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
            PermissionConstant::UPDATE_FACTION_PERMISSION
        ]
    ];

    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string
    {
        return self::SLUG;
    }
    
    public function __invoke(Player $Player, UserEntity $User, array $UserPermissions, ?array $params = null){
        $this->UserEntity = $User;
        $message = '';
        if (isset($params[0])) $message = $params[0];

        $menu = $this->mainMenu($message);
        $Player->sendForm($menu);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            return Utils::processMenu(RouterFactory::get(UpdateFaction::SLUG), $Player, [
                $data[1],
                function (string $factionName, RewardInterface $reward, $value) use ($Player) {
                    if ($reward->executeGet($factionName, $value)) {
                        Utils::processMenu(RouterFactory::get(AdminToolsMain::SLUG), $Player, [Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_SUCCESS", ['rewardName' => Utils::getText($Player->getName(), $reward->getName($Player->getName())), "factionName" => $factionName])] );
                    }else{
                        Utils::processMenu(RouterFactory::get(AdminToolsMain::SLUG), $Player, [Utils::getText($this->UserEntity->name, "ERROR")]);
                    }
                },
                AdminToolsMain::SLUG
            ]);
        };
    }

    private function mainMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->addLabel($message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_INFORMATION"), Utils::getText($this->UserEntity->name, "ADMIN_TOOLS_UPDATE_FACTION_PLACEHOLDER"));
        return $menu;
    }
}