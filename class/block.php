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
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>>
 * @version     $Id: block.php,v 1.25 2008/01/27 20:08:26 blindman1344 Exp $
 */

define('FEATUREDPHOTO_BLOCK_MODE_INCREMENT',         0);
define('FEATUREDPHOTO_BLOCK_MODE_RANDOM',            1);
define('FEATUREDPHOTO_BLOCK_MODE_FIXED',             2);
define('FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST',     3);
define('FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST_SET', 4);
define('FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET', 5);

class FeaturedPhoto_Block
{
    var $id            = 0;
    var $key_id        = 0;
    var $title         = NULL;
    var $mode          = 0;
    var $active        = 1;
    var $current_photo = 0;
    var $flickr_set    = 0;
    var $tn_width      = FEATUREDPHOTO_DEFAULT_THUMBNAIL_WIDTH;
    var $tn_height     = FEATUREDPHOTO_DEFAULT_THUMBNAIL_HEIGHT;
    var $template      = FEATUREDPHOTO_DEFAULT_BLOCK_TEMPLATE;
    var $_pin_key      = NULL;


    function FeaturedPhoto_Block($id=NULL)
    {
        if (empty($id))
        {
            return;
        }
        $this->setId($id);

        $db = new PHPWS_DB('featuredphoto_blocks');
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

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    function getLayoutContentVar()
    {
        return 'featuredphoto_block_' . $this->id;
    }

    function setMode($mode)
    {
        $this->mode = (int)$mode;
    }

    function getMode($format=true)
    {
        if ($format)
        {
            $modes = $this->allModes();

            foreach ($modes as $num => $name)
            {
                if ($num == $this->mode)
                {
                    return $name;
                }
            }

            return dgettext('featuredphoto', 'N/A');
        }

        return $this->mode;
    }

    function setCurrentPhoto($current_photo)
    {
        $this->current_photo = (is_numeric($current_photo) ? $current_photo : 0);
    }

    function setFlickrSet($flickr_set)
    {
        $this->flickr_set = (is_numeric($flickr_set) ? $flickr_set : 0);
    }

    function setTnWidth($width)
    {
        $this->tn_width = (int)$width;
    }

    function getTnWidth()
    {
        return $this->tn_width;
    }

    function setTnHeight($height)
    {
        $this->tn_height = (int)$height;
    }

    function getTnHeight()
    {
        return $this->tn_height;
    }

    function setTemplate($template)
    {
        $this->template = $template;
    }

    function getTemplate()
    {
        return $this->template;
    }

    function getActive()
    {
        $active = dgettext('featuredphoto', 'Active');
        $inactive = dgettext('featuredphoto', 'Inactive');

        if (Current_User::allow('featuredphoto', 'hide_blocks', $this->getId()))
        {
            $vars['block_id'] = $this->getId();
            $vars['action'] = 'hideBlock';
            return PHPWS_Text::secureLink(($this->active ? $active : $inactive), 'featuredphoto', $vars);
        }

        return ($this->active ? $active : $inactive);
    }

    function setPinKey($key)
    {
        $this->_pin_key = $key;
    }

    function getKey()
    {
        $key = new Key($this->key_id);
        return $key;
    }

    function allModes()
    {
        $modes[FEATUREDPHOTO_BLOCK_MODE_INCREMENT] = dgettext('featuredphoto', 'Increment');
        $modes[FEATUREDPHOTO_BLOCK_MODE_RANDOM]    = dgettext('featuredphoto', 'Random');
        $modes[FEATUREDPHOTO_BLOCK_MODE_FIXED]     = dgettext('featuredphoto', 'Fixed on the image');

        if (PHPWS_Settings::get('featuredphoto', 'flickr_support'))
        {
            $modes[FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST]     = dgettext('featuredphoto',
                                                                          'Flickr: Latest public photo');
            $modes[FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST_SET] = dgettext('featuredphoto',
                                                                          'Flickr: Latest public photo in set');
            $modes[FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET] = dgettext('featuredphoto',
                                                                          'Flickr: Random public photo in set');
        }

        return $modes;
    }

    function isModeFlickr()
    {
        if (($this->mode == FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST) ||
            ($this->mode == FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST_SET) ||
            ($this->mode == FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET))
        {
            return true;
        }

        return false;
    }

    function save($save_key=TRUE)
    {
        $db = new PHPWS_DB('featuredphoto_blocks');
        $result = $db->saveObject($this);
        if (PEAR::isError($result))
        {
            return $result;
        }

        if ($save_key)
        {
            return $this->saveKey();
        }
    }

    function saveKey()
    {
        if (empty($this->key_id))
        {
            $key = new Key;
            $key->module = 'featuredphoto';
            $key->item_name = 'block';
            $key->item_id = $this->id;
            $key->url = 'index.php?module=featuredphoto&blk_id=' . $this->id;
        }
        else
        {
            $key = new Key($this->key_id);
        }

        $key->edit_permission = 'edit_blocks';
        $key->title = $this->title;
        $result = $key->save();
        if (PEAR::isError($result))
        {
            return $result;
        }

        if (empty($this->key_id))
        {
            $this->key_id = $key->id;
            $this->save(FALSE);
        }
    }

    function toggle()
    {
        if (!Current_User::authorized('featuredphoto', 'hide_blocks', $this->id))
        {
            Current_User::disallow();
            return;
        }

        $this->active = ($this->active ? 0 : 1);
        return $this->save(FALSE);
    }

    function clearPins()
    {
        $db = new PHPWS_DB('featuredphoto_pins');
        $db->addWhere('block_id', $this->id);
        $db->delete();
    }

    function clearPhotos()
    {
        PHPWS_Core::initModClass('featuredphoto', 'photo.php');

        $db = new PHPWS_DB('featuredphoto_photos');
        $db->addWhere('block_id', $this->id);
        $photos = $db->getObjects('FeaturedPhoto');

        if (!PHPWS_Error::logIfError($photos) && ($photos != NULL))
        {
            foreach ($photos as $pic)
            {
                $pic->kill();
            }
        }
    }

    function kill()
    {
        if (!Current_User::authorized('featuredphoto', 'delete_blocks', $this->id))
        {
            Current_User::disallow();
            return;
        }

        $this->clearPins();
        $this->clearPhotos();
        $db = new PHPWS_DB('featuredphoto_blocks');
        $db->addWhere('id', $this->id);

        $result = $db->delete();
        PHPWS_Error::logIfError($result);

        $key = new Key($this->key_id);
        $result = $key->delete();
        PHPWS_Error::logIfError($result);
    }

    function getPhoto()
    {
        switch ($this->getMode(false))
        {
            case FEATUREDPHOTO_BLOCK_MODE_RANDOM:
            {
                PHPWS_Core::initModClass('featuredphoto', 'photo.php');

                $db = new PHPWS_DB('featuredphoto_photos');
                $db->addWhere('active', 1);
                $db->addWhere('block_id', $this->getId());
                $db->addOrder('random');
                $db->setLimit(1);
                $photos = $db->getObjects('FeaturedPhoto');
                if (!PHPWS_Error::logIfError($photos) && ($photos != NULL))
                {
                    return $photos[0]->view();
                }
                break;
            }

            case FEATUREDPHOTO_BLOCK_MODE_FIXED:
            {
                if ($this->current_photo > 0)
                {
                    PHPWS_Core::initModClass('featuredphoto', 'photo.php');

                    $db = new PHPWS_DB('featuredphoto_photos');
                    $db->addWhere('active', 1);
                    $db->addWhere('block_id', $this->getId());
                    $db->addWhere('id', $this->current_photo);
                    $db->setLimit(1);
                    $photos = $db->getObjects('FeaturedPhoto');
                    if (!PHPWS_Error::logIfError($photos) && ($photos != NULL))
                    {
                        return $photos[0]->view();
                    }
                }
                break;
            }

            case FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST:
            {
                if (PHPWS_Settings::get('featuredphoto', 'flickr_support'))
                {
                    PHPWS_Core::initModClass('featuredphoto', 'lib/Flickr.php');

                    $f = new PHPWS_Flickr(PHPWS_Settings::get('featuredphoto', 'flickr_api_key'));
                    $person = $f->people_findByUsername(PHPWS_Settings::get('featuredphoto', 'flickr_username'));
                    if (!PHPWS_Error::logIfError($person))
                    {
                        // Get the friendly URL of the user's photos
                        $photos_url = $f->urls_getUserPhotos($person['id']);
                        // Get the user's latest public photo
                        $photos = $f->people_getPublicPhotos($person['id'], NULL, 1);

                        if (!PHPWS_Error::logIfError($photos_url) && !PHPWS_Error::logIfError($photos))
                        {
                            $photo = $photos['photo'][0];
                            $info = $f->photos_getInfo($photo['id'], $photo['secret']);
                            if (!PHPWS_Error::logIfError($info))
                            {
                                $template['URL']       = $photos_url . $photo['id'];
                                $template['IMAGE_SRC'] = $f->build_photo_url($photo,
                                                             $f->best_photo_size($this->tn_width, $this->tn_height));
                                $template['IMAGE_ALT'] = $photo['title'];
                                $template['NAME']      = $photo['title'];
                                $template['CAPTION']   = !empty($info['description']) ? $info['description'] : NULL;
                                $template['MORE_URL']  = $photos_url;
                                $template['MORE']      = dgettext('featuredphoto', 'More Photos');
                                return PHPWS_Template::process($template, 'featuredphoto', 'photo/flickr_view.tpl');
                            }
                        }
                    }
                }
                break;
            }

            case FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST_SET:
            case FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET:
            {
                if (PHPWS_Settings::get('featuredphoto', 'flickr_support') && ($this->flickr_set > 0))
                {
                    PHPWS_Core::initModClass('featuredphoto', 'lib/Flickr.php');

                    $f = new PHPWS_Flickr(PHPWS_Settings::get('featuredphoto', 'flickr_api_key'));
                    $person = $f->people_findByUsername(PHPWS_Settings::get('featuredphoto', 'flickr_username'));
                    $photoset = $f->photosets_getInfo($this->flickr_set);
                    if (!PHPWS_Error::logIfError($person) && !PHPWS_Error::logIfError($photoset) &&
                        ($photoset['photos'] > 0))
                    {
                        // Get the friendly URL of the user's photos
                        $photos_url = $f->urls_getUserPhotos($person['id']);

                        // Get the user's photo
                        $page = (($this->mode == FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET) ?
                                 rand(1, $photoset['photos']) : $photoset['photos']);
                        $photos = $f->photosets_getPhotos($this->flickr_set, NULL, 1, 1, $page);

                        if (!PHPWS_Error::logIfError($photos_url) && !PHPWS_Error::logIfError($photos))
                        {
                            $photo = $photos['photo'][0];
                            $info = $f->photos_getInfo($photo['id'], $photo['secret']);
                            if (!PHPWS_Error::logIfError($info))
                            {
                                $template['URL']       = $photos_url . $photo['id'];
                                $template['IMAGE_SRC'] = $f->build_photo_url($photo,
                                                             $f->best_photo_size($this->tn_width, $this->tn_height));
                                $template['IMAGE_ALT'] = $photo['title'];
                                $template['NAME']      = $photo['title'];
                                $template['CAPTION']   = !empty($info['description']) ? $info['description'] : NULL;
                                $template['MORE_URL']  = $photos_url . 'sets/' . $this->flickr_set;
                                $template['MORE']      = dgettext('featuredphoto', 'More Photos');
                                return PHPWS_Template::process($template, 'featuredphoto', 'photo/flickr_view.tpl');
                            }
                        }
                    }
                }
                break;
            }

            default:
            {
                PHPWS_Core::initModClass('featuredphoto', 'photo.php');

                $db = new PHPWS_DB('featuredphoto_photos');
                $db->addWhere('active', 1);
                $db->addWhere('block_id', $this->getId());
                $db->addOrder('id');
                $photos = $db->getObjects('FeaturedPhoto');

                if (!PHPWS_Error::logIfError($photos) && ($photos != NULL))
                {
                    $newFound = false;

                    foreach ($photos as $pic)
                    {
                        if (($pic->getId() > $this->current_photo) && (!$newFound))
                        {
                            $newFound = true;
                            $photo = $pic;
                            $this->current_photo = $pic->getId();
                        }
                    }

                    if (!$newFound)
                    {
                        $newFound = true;
                        $photo = &$photos[0];
                        $this->current_photo = $photo->getId();
                    }

                    $this->save(false);
                    return $photo->view();
                }
                break;
            }
        }

        return dgettext('featuredphoto', 'No active photo.');
    }

    function view($pin_mode=FALSE, $admin_icon=TRUE)
    {
        $opt = NULL;
        $edit = NULL;

        if ($this->active && ($this->getTitle() != NULL))
        {
            if (Current_User::allow('featuredphoto'))
            {
                $link['block_id'] = $this->getId();

                if (!$this->isModeFlickr())
                {
                    $link['action'] = 'managePhotos';
                    $img = sprintf('<img src="./images/mod/featuredphoto/edit.png" alt="%s" title="%s" />',
                                   dgettext('featuredphoto', 'Manage Photos'),
                                   dgettext('featuredphoto', 'Manage Photos'));
                    $edit = PHPWS_Text::secureLink($img, 'featuredphoto', $link);
                }

                if (Current_User::allow('featuredphoto', 'pin_blocks'))
                {
                    if (!empty($this->_pin_key) && $pin_mode)
                    {
                        $link['action'] = 'lockBlock';
                        $link['key_id'] = $this->_pin_key->id;
                        $img = sprintf('<img src="./images/mod/featuredphoto/pin.png" alt="%s" title="%s" />',
                                       dgettext('featuredphoto', 'Pin'), dgettext('featuredphoto', 'Pin'));
                        $opt = PHPWS_Text::secureLink($img, 'featuredphoto', $link);
                    }
                    elseif (!empty($this->_pin_key) && $admin_icon)
                    {
                        $link['action'] = 'removeBlockPin';
                        $link['key_id'] = $this->_pin_key->id;
                        $js_var['ADDRESS'] = PHPWS_Text::linkAddress('featuredphoto', $link, TRUE);
                        $js_var['QUESTION'] = dgettext('featuredphoto',
                                              'Are you sure you want to remove this photo block from this page?');
                        $js_var['LINK'] = sprintf('<img src="./images/mod/featuredphoto/remove.png" alt="%s" title="%s" />',
                                                  dgettext('featuredphoto', 'Remove'), dgettext('featuredphoto', 'Remove'));

                        $opt = Layout::getJavascript('confirm', $js_var);
                    }
                }
            }

            $template = array('TITLE' => $this->getTitle(), 'CONTENT' => $this->getPhoto(), 'OPT'=> $opt, 'EDIT'=> $edit);
            return PHPWS_Template::process($template, 'featuredphoto', 'block/boxstyles/' . $this->getTemplate());
        }

        return NULL;
    }

    function isPinned()
    {
        if (!isset($_SESSION['Pinned_Photo_Blocks']))
        {
            return FALSE;
        }

        return isset($_SESSION['Pinned_Photo_Blocks'][$this->id]);
    }

    function allPinned()
    {
        static $all_pinned = null;

        if (empty($all_pinned))
        {
            $db = new PHPWS_DB('featuredphoto_pins');
            $db->addWhere('key_id', -1);
            $db->addColumn('block_id');
            $result = $db->select('col');
            if (!PHPWS_Error::logIfError($result))
            {
                if ($result)
                {
                    $all_pinned = $result;
                }
                else
                {
                    $all_pinned = true;
                }
            }
        }

        if (is_array($all_pinned))
        {
            return in_array($this->id, $all_pinned);
        }

        return false;
    }

    function getListTags()
    {
        $links = array();
        $vars['block_id'] = $this->getId();

        if (!$this->isModeFlickr())
        {
            $vars['action'] = 'managePhotos';
            $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Photos'), 'featuredphoto', $vars);
        }

        if (Current_User::allow('featuredphoto', 'edit_blocks', $this->id))
        {
            $vars['action'] = 'editBlock';
            $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Edit'), 'featuredphoto', $vars);
        }

        if (Current_User::allow('featuredphoto', 'pin_blocks'))
        {
            if ($this->isPinned())
            {
                $vars['action'] = 'unpinBlock';
                $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Unpin'), 'featuredphoto', $vars);
            }
            else if ($this->allPinned())
            {
                $vars['action'] = 'removeBlockPin';
                $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Unpin all'), 'featuredphoto', $vars);
            }
            else
            {
                $vars['action'] = 'pinBlock';
                $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Pin'), 'featuredphoto', $vars);
                $vars['action'] = 'pinBlockAll';
                $links[] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Pin all'), 'featuredphoto', $vars);
            }
        }

        if (Current_User::isUnrestricted('featuredphoto'))
        {
            $links[] = Current_User::popupPermission($this->key_id);
        }

        if (Current_User::allow('featuredphoto', 'delete_blocks'))
        {
            $vars['action'] = 'deleteBlock';
            $confirm_vars['QUESTION'] = dgettext('featuredphoto', 'Are you sure you want to permanently delete this photo block?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('featuredphoto', $vars, TRUE);
            $confirm_vars['LINK'] = dgettext('featuredphoto', 'Delete');
            $links[] = javascript('confirm', $confirm_vars);
        }

        $template['ACTION'] = implode(' | ', $links);
        $template['TITLE'] = $this->getTitle();
        $template['MODE'] = $this->getMode();
        $template['ACTIVE'] = $this->getActive();

        return $template;
    }
}

?>