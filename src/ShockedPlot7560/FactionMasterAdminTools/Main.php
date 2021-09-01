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

namespace ShockedPlot7560\FactionMasterAdminTools;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionFac;
use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionNoFac;
use ShockedPlot7560\FactionMaster\Extension\Extension;
use ShockedPlot7560\FactionMaster\Main as FactionMasterMain;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use ShockedPlot7560\FactionMasterAdminTools\Button\Collection\AdminToolsMain as CollectionAdminToolsMain;
use ShockedPlot7560\FactionMasterAdminTools\Route\AdminToolsMain;
use ShockedPlot7560\FactionMasterAdminTools\Route\ClaimSelect;
use ShockedPlot7560\FactionMasterAdminTools\Route\DeleteClaim;
use ShockedPlot7560\FactionMasterAdminTools\Route\DeleteFaction;
use ShockedPlot7560\FactionMasterAdminTools\Route\DeleteHome;
use ShockedPlot7560\FactionMasterAdminTools\Route\DeleteInvitation;
use ShockedPlot7560\FactionMasterAdminTools\Route\HomeSelect;
use ShockedPlot7560\FactionMasterAdminTools\Route\TpClaim;
use ShockedPlot7560\FactionMasterAdminTools\Route\TpHome;
use ShockedPlot7560\FactionMasterAdminTools\Route\UpdateFaction;
use ShockedPlot7560\FactionMasterAdminTools\Route\UpdateFactionSelect;

class Main extends PluginBase implements Extension, PermissionConstant{

    private $LangConfig = [];
    private static $instance;

    const ADMIN_TOOLS_SLUG = "adminToolsMainButton";

    public function onLoad(): void {
        self::$instance = $this;
        FactionMasterMain::getInstance()->getExtensionManager()->registerExtension($this);

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->saveResource('fr_FR.yml');
        $this->saveResource('en_EN.yml');
        $this->saveResource('config.yml');
        $this->config = new Config($this->getDataFolder() . "config.yml");
        $this->LangConfig = [
            "fr_FR" => new Config($this->getDataFolder() . "fr_FR.yml", Config::YAML),
            "en_EN" => new Config($this->getDataFolder() . "en_EN.yml", Config::YAML)
        ];
    }

    public function execute(): void {

        $this->registerRoute();
        $this->registerCollection();

        foreach ([
            CollectionFactory::get(MainCollectionFac::SLUG),
            CollectionFactory::get(MainCollectionNoFac::SLUG)
        ] as $collection) {
            $collection->registerCallable("FactionMasterAdminTools", function() use ($collection) {
                $collection->register(new Button(
                    self::ADMIN_TOOLS_SLUG,
                    function (string $Player) {
                        return Utils::getText($Player, "BUTTON_MAIN_ADMIN_TOOLS");
                    },
                    function (Player $Player) {
                        Utils::processMenu(RouterFactory::get(AdminToolsMain::SLUG), $Player);
                    },[
                        [
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::DELETE_FACTION_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::DELETE_CLAIM_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::DELETE_HOME_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::DELETE_INVITATION_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::UPDATE_FACTION_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::TP_HOME_PERMISSION
                        ],[
                            Utils::POCKETMINE_PERMISSIONS_CONSTANT,
                            self::TP_CLAIM_PERMISSION
                        ]
                    ]
                ), 0);
            });        
        }
    }

    public function getLangConfig(): array {
        return $this->LangConfig;
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public function getExtensionName() : string {
        return 'FactionMaster-AdminTools';
    }

    public static function getConfigF(string $key) {
        $Config = new Config(self::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        return $Config->get($key);
    }

    private function registerCollection() {
        $collections = [CollectionAdminToolsMain::class];
        foreach ($collections as $collection) {
            CollectionFactory::register(new $collection);
        }
    }

    private function registerRoute() {
        $routes = [
            AdminToolsMain::class, 
            DeleteFaction::class, 
            DeleteInvitation::class, 
            DeleteHome::class, 
            HomeSelect::class,
            UpdateFaction::class,
            UpdateFactionSelect::class,
            ClaimSelect::class,
            DeleteClaim::class,
            TpHome::class,
            TpClaim::class
        ];
        foreach ($routes as $route) {
            RouterFactory::registerRoute(new $route);
        }
    }
}