<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\Solid;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityPistonArm;

use redstone\utils\Facing;

class BlockPiston extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::PISTON;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $damage = 0;
        if($player !== null) {
            $faces = [5, 3, 4, 2];
            $damage = $faces[$player->getDirection()];
            if ($player->getPitch() > 45) {
                $damage = 1;
            } else if ($player->getPitch() < -45) {
                $damage = 0;
            }
        }

        $this->setDamage($damage);
        $this->level->setBlock($this, $this, true, true);

        $nbt = BlockEntityPistonArm::createNBT($this);
        $nbt->setByte("Sticky", $this->isSticky() ? 1 : 0);
        Tile::createTile("BlockEntityPistonArm", $this->getLevel(), $nbt);

        return true;
    }
    
    public function getBlockEntity() : BlockEntityPistonArm {
        $tile = $this->getLevel()->getTile($this);
        $arm = null;
        if($tile instanceof BlockEntityPistonArm){
            $arm = $tile;
        }else{
            $nbt = BlockEntityPistonArm::createNBT($this);
            $nbt->setByte("Sticky", $this->isSticky() ? 1 : 0);
            $arm = Tile::createTile("BlockEntityPistonArm", $this->getLevel(), $nbt);
        }
        return $arm;
    }

    public function isSticky() : bool {
        return false;
    }

    public function getFace() : int {
        return Facing::opposite($this->getDamage());
    }
    
    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 0;
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        $this->getBlockEntity()->extend($this->isBlockPowered($this->asVector3()));
    }
}