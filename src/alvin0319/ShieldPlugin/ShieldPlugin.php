<?php

/*
 *       _       _        ___ _____ _  ___
 *   __ _| |_   _(_)_ __  / _ \___ // |/ _ \
 * / _` | \ \ / / | '_ \| | | ||_ \| | (_) |
 * | (_| | |\ V /| | | | | |_| |__) | |\__, |
 *  \__,_|_| \_/ |_|_| |_|\___/____/|_|  /_/
 *
 * Copyright (C) 2020 alvin0319
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
namespace alvin0319\ShieldPlugin;

use alvin0319\OffHand\OffHandPlayer;
use alvin0319\ShieldPlugin\item\Shield;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

class ShieldPlugin extends PluginBase implements Listener{

	public function onEnable(){
		ItemFactory::registerItem(new Shield());
		Item::addCreativeItem(new Shield());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function handleToggleSneak(PlayerToggleSneakEvent $event){
		$player = $event->getPlayer();

		if($player->getInventory()->getItemInHand() instanceof Shield){
			if($event->isSneaking()){
				$player->setGenericFlag(Entity::DATA_FLAG_BLOCKING, true);
			}else{
				$player->setGenericFlag(Entity::DATA_FLAG_BLOCKING, false);
			}
		}elseif($this->getServer()->getPluginManager()->getPlugin("OffHand") instanceof Plugin){
			if(($item = $player->getOffHandInventory()->getItemInOffHand()) instanceof Shield){
				if($event->isSneaking()){
					$player->setGenericFlag(Entity::DATA_FLAG_BLOCKING, true);
				}else{
					$player->setGenericFlag(Entity::DATA_FLAG_BLOCKING, false);
				}
			}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority HIGHEST
	 */
	public function onEntityDamageEvent(EntityDamageEvent $event) : void{
		if($event instanceof EntityDamageByEntityEvent){
			$d = $event->getDamager();
			/** @var OffHandPlayer|Player $e */
			$e = $event->getEntity();
			if($e instanceof Player){
				/** @var Shield $item */
				if(($item = $e->getInventory()->getItemInHand()) instanceof Shield){
					if($e->getGenericFlag(Entity::DATA_FLAG_BLOCKING)){
						$val = floor(abs($d->yaw - $e->yaw) / 2);
						if($val >= 50 and $val <= 110){
							$e->getLevel()->broadcastLevelSoundEvent($e, LevelSoundEventPacket::SOUND_ITEM_SHIELD_BLOCK);
							//$event->setCancelled();
							$finalDamage = $event->getFinalDamage();
							if($finalDamage >= 4){
								$item->applyDamage(1);
								$e->getInventory()->setItemInHand($item);
								$event->setBaseDamage($finalDamage * 0.3);
							}else{
								$event->setCancelled();
							}
						}
					}
				}elseif($this->getServer()->getPluginManager()->getPlugin("OffHand") instanceof Plugin){
					if(($item = $e->getOffHandInventory()->getItemInOffHand()) instanceof Shield){
						if($e->getGenericFlag(Entity::DATA_FLAG_BLOCKING)){
							$val = floor(abs($d->yaw - $e->yaw) / 2);
							if($val >= 50 and $val <= 110){
								$e->getLevel()->broadcastLevelSoundEvent($e, LevelSoundEventPacket::SOUND_ITEM_SHIELD_BLOCK);
								//$event->setCancelled();
								$finalDamage = $event->getFinalDamage();
								if($finalDamage >= 4){
									$item->applyDamage(1);
									$e->getOffHandInventory()->setItemInOffHand($item);
									$event->setBaseDamage($finalDamage * 0.3);
								}else{
									$event->setCancelled();
								}
							}
						}
					}
				}
			}
		}
	}
}