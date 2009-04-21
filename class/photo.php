<?php
/**
 * featuredphoto
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
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
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: photo.php,v 1.16 2008/03/30 14:18:16 blindman1344 Exp $
 */

class FeaturedPhoto
{
    var $id          = 0;
    var $block_id    = 0;
    var $image_id    = 0;
    var $active      = 1;


    function FeaturedPhoto($id=NULL)
    {
        if (empty($id))
        {
            return;
        }
        $this->setId($id);

        $db = new PHPWS_DB('featuredphoto_photos');
        $db->loadObject($this);
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setBlockId($block_id)
    {
        $this->block_id = (int)$block_id;
    }

    function getBlockId()
    {
        return $this->block_id;
    }

    function setImageId($image_id)
    {
        $this->image_id = (int)$image_id;
    }

    function getImageId()
    {
        return $this->image_id;
    }

    function getActive()
    {
        $vars['block_id'] = $this->getBlockId();
        $vars['photo_id'] = $this->getId();

        $active = dgettext('featuredphoto', 'Active');
        $inactive = dgettext('featuredphoto', 'Inactive');

        if (Current_User::allow('featuredphoto', 'hide_photos', $this->id))
        {
            $vars['action'] = 'hidePhoto';
            return PHPWS_Text::secureLink(($this->active ? $active : $inactive), 'featuredphoto', $vars);
        }
        else
        {
            return ($this->active ? $active : $inactive);
        }
    }

    function toggle()
    {
        if (!Current_User::authorized('featuredphoto', 'hide_photos', $this->id))
        {
            Current_User::disallow();
            return;
        }

        $this->active = ($this->active ? 0 : 1);
        return $this->save();
    }

    function save()
    {
        $db = new PHPWS_DB('featuredphoto_photos');
        $result = $db->saveObject($this);
        if (PEAR::isError($result))
        {
            return $result;
        }
    }

    function kill()
    {
        if (!Current_User::authorized('featuredphoto', 'delete_photos', $this->id))
        {
            Current_User::disallow();
            return;
        }

        $db = new PHPWS_DB('featuredphoto_photos');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        return !PHPWS_Error::logIfError($result);
    }

    function view()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $image = Cabinet::getFile($this->getImageId());
        $image->allowCaption(false);

        $tags['NAME'] = $image->_source->title;
        $tags['CAPTION'] = PHPWS_Text::parseOutput($image->_source->description);
        $tags['IMAGE'] = $image->parentLinked();

        return PHPWS_Template::process($tags, 'featuredphoto', 'photo/view.tpl');
    }

    function getListTags()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

        $image = Cabinet::getFile($this->getImageId());
        $vars['photo_id'] = $this->getId();

        $vars['action'] = 'viewPhoto';
        $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'View'), 'featuredphoto', $vars);

        if (Current_User::allow('featuredphoto', 'edit_photos', $this->id))
        {
            $vars['action'] = 'editPhoto';
            $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Edit'), 'featuredphoto', $vars);
        }

        if (Current_User::allow('featuredphoto', 'delete_photos'))
        {
            $vars['action'] = 'deletePhoto';
            $confirm_vars['QUESTION'] = dgettext('featuredphoto', 'Are you sure you want to remove this photo from this photo block?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('featuredphoto', $vars, TRUE);
            $confirm_vars['LINK'] = dgettext('featuredphoto', 'Remove');
            $links[] = javascript('confirm', $confirm_vars);
        }

        $template['ACTION'] = implode(' | ', $links);
        $template['NAME'] = $image->_source->title;
        $template['SIZE'] = $image->_source->getSize(true);
        $template['ACTIVE'] = $this->getActive();

        return $template;
    }

}// END CLASS FeaturedPhoto

?>