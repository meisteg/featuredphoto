<?php
/**
 * Copyright (c) 2004-2009 Gregory Meiste
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package FeaturedPhoto
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

PHPWS_Core::initModClass('featuredphoto', 'block.php');

class FeaturedPhoto_Runtime
{
    function show()
    {
        FeaturedPhoto_Runtime::showAllBlocks();

        $key = Key::getCurrent();
        if (!empty($key) && !$key->isDummy(true))
        {
            FeaturedPhoto_Runtime::showBlocks($key);
            FeaturedPhoto_Runtime::viewPinnedBlocks($key);
        }
    }

    function showAllBlocks()
    {
        $key = new Key;
        $key->id = -1;
        FeaturedPhoto_Runtime::showBlocks($key);
    }

    function viewPinnedBlocks($key)
    {
        if (isset($_SESSION['Pinned_Photo_Blocks']))
        {
            $block_list = &$_SESSION['Pinned_Photo_Blocks'];
            if (!empty($block_list))
            {
                foreach ($block_list as $block_id => $block)
                {
                    if (!isset($GLOBALS['Current_Photo_Blocks'][$block_id]))
                    {
                        $block->setPinKey($key);
                        $content[] = $block->view(TRUE);
                    }
                }

                if (!empty($content))
                {
                    $complete = implode('', $content);
                    Layout::add($complete, 'featuredphoto', 'Photo_Block_List');
                }
            }
        }
    }

    function showBlocks($key)
    {
        $db = new PHPWS_DB('featuredphoto_blocks');
        $db->addWhere('featuredphoto_pins.key_id', $key->id);
        $db->addWhere('id', 'featuredphoto_pins.block_id');
        Key::restrictView($db, 'featuredphoto');
        $result = $db->getObjects('FeaturedPhoto_Block');

        if (!PHPWS_Error::logIfError($result) && !empty($result))
        {
            foreach ($result as $block)
            {
                $block->setPinKey($key);
                Layout::add($block->view(), 'featuredphoto', $block->getLayoutContentVar());
                $GLOBALS['Current_Photo_Blocks'][$block->id] = TRUE;
            }
        }
    }
}

?>