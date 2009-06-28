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

PHPWS_Core::initModClass('featuredphoto', 'photo.php');

class FeaturedPhoto_Manager
{
    function action()
    {
        if (!Current_User::allow('featuredphoto'))
        {
            Current_User::disallow();
            return;
        }

        $panel = & FeaturedPhoto_Manager::cpanel();
        if (isset($_REQUEST['action']))
        {
            $action = $_REQUEST['action'];
        }
        else
        {
            $tab = $panel->getCurrentTab();
            if (empty($tab))
            {
                $action = 'manageBlocks';
            }
            else
            {
                $action = &$tab;
            }
        }

        $panel->setContent(FeaturedPhoto_Manager::route($action, $panel));
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $linkBase = 'index.php?module=featuredphoto';

        $tabs['manageBlocks'] = array ('title'=>dgettext('featuredphoto', 'Manage Photo Blocks'), 'link'=> $linkBase);
        if (Current_User::allow('featuredphoto', 'edit_blocks'))
        {
            $tabs['newBlock'] = array ('title'=>dgettext('featuredphoto', 'New Photo Block'), 'link'=> $linkBase);
        }
        if (Current_User::allow('featuredphoto', 'edit_settings'))
        {
            $tabs['editSettings'] = array ('title'=>dgettext('featuredphoto', 'Settings'), 'link'=> $linkBase);
        }

        $panel = new PHPWS_Panel('featuredphoto');
        $panel->enableSecure();
        $panel->quickSetTabs($tabs);

        $panel->setModule('featuredphoto');
        return $panel;
    }

    function route($action, &$panel)
    {
        $title   = NULL;
        $content = NULL;
        $message = FeaturedPhoto_Manager::getMessage();

        if (isset($_REQUEST['block_id']))
        {
            $block = new FeaturedPhoto_Block($_REQUEST['block_id']);
        }
        else
        {
            $block = new FeaturedPhoto_Block();
        }

        if (isset($_REQUEST['photo_id']))
        {
            $photo = new FeaturedPhoto($_REQUEST['photo_id']);
        }
        else
        {
            $photo = new FeaturedPhoto();
            if (isset($_REQUEST['block_id']))
            {
                $photo->setBlockId($_REQUEST['block_id']);
            }
        }

        switch ($action)
        {
            /***************** BEGIN PHOTO BLOCK CASES ****************/

            case 'manageBlocks':
                /* Need to set tab in case we got here from another action. */
                $panel->setCurrentTab('manageBlocks');
                $title = dgettext('featuredphoto', 'Manage Photo Blocks');
                $content = FeaturedPhoto_Manager::listBlocks();
                break;

            case 'newBlock':
                $title = dgettext('featuredphoto', 'New Photo Block');
                $content = FeaturedPhoto_Manager::editBlock($block);
                break;

            case 'editBlock':
                $title = dgettext('featuredphoto', 'Edit Photo Block');
                $content = FeaturedPhoto_Manager::editBlock($block);
                break;

            case 'editJSBlock':
                $template['TITLE'] = dgettext('featuredphoto', 'New Photo Block');
                $template['CONTENT'] = FeaturedPhoto_Manager::editBlock($block, true);
                $content = PHPWS_Template::process($template, 'featuredphoto', 'admin.tpl');
                Layout::nakedDisplay($content);
                break;

            case 'postBlock':
                $result = FeaturedPhoto_Manager::postBlock($block);
                if (is_array($result))
                {
                    $title = dgettext('featuredphoto', 'Edit Photo Block');
                    $content = FeaturedPhoto_Manager::editBlock($block, false, $result);
                }
                else
                {
                    FeaturedPhoto_Manager::checkPermission('edit_blocks', $block);
                    $result = $block->save();
                    if (PHPWS_Error::logIfError($result))
                    {
                        FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block could not be saved'),
                                                           'manageBlocks');
                    }
                    else
                    {
                        FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block saved'),
                                                           'manageBlocks');
                    }
                }
                break;

            case 'postJSBlock':
                $result = FeaturedPhoto_Manager::postBlock($block);
                if (is_array($result))
                {
                    $template['TITLE'] = dgettext('featuredphoto', 'Edit Photo Block');
                    $template['CONTENT'] = FeaturedPhoto_Manager::editBlock($block, true, $result);
                    $content = PHPWS_Template::process($template, 'featuredphoto', 'admin.tpl');
                    Layout::nakedDisplay($content);
                }
                else
                {
                    FeaturedPhoto_Manager::checkPermission('edit_blocks', $block);
                    $result = $block->save();
                    if (!PHPWS_Error::logIfError($result) && isset($_REQUEST['key_id']))
                    {
                        FeaturedPhoto_Manager::lockBlock($block->id, $_REQUEST['key_id']);
                    }

                    javascript('close_refresh');
                }
                break;

            case 'hideBlock':
                $result = $block->toggle();
                if (PHPWS_Error::logIfError($result))
                {
                    FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto',
                        'Photo block activation could not be changed'), 'manageBlocks');
                }
                else
                {
                    FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto',
                        'Photo block activation changed'), 'manageBlocks');
                }
                break;

            case 'pinBlock':
                FeaturedPhoto_Manager::checkPermission('pin_blocks');
                $_SESSION['Pinned_Photo_Blocks'][$block->getId()] = $block;
                FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block pinned'), 'manageBlocks');
                break;

            case 'pinBlockAll':
                FeaturedPhoto_Manager::pinBlockAll($block->getId());
                FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block pinned'), 'manageBlocks');
                break;

            case 'unpinBlock':
                unset($_SESSION['Pinned_Photo_Blocks'][$block->getId()]);
                FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block unpinned'), 'manageBlocks');
                break;

            case 'deleteBlock':
                $block->kill();
                FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo block deleted'), 'manageBlocks');
                break;

            case 'lockBlock':
                FeaturedPhoto_Manager::checkPermission('pin_blocks');
                $result = FeaturedPhoto_Manager::lockBlock($_GET['block_id'], $_GET['key_id']);
                PHPWS_Error::logIfError($result);
                PHPWS_Core::goBack();
                break;

            case 'removeBlockPin':
                FeaturedPhoto_Manager::removeBlockPin();
                PHPWS_Core::goBack();
                break;

            /****************** END PHOTO BLOCK CASES *****************/
            /******************** BEGIN PHOTO CASES *******************/

            case 'managePhotos':
                $title = dgettext('featuredphoto', 'Manage Photos');
                $content = FeaturedPhoto_Manager::listPhotos($_GET['block_id']);
                break;

            case 'newPhoto':
                $title = dgettext('featuredphoto', 'New Photo');
                $content = FeaturedPhoto_Manager::editPhoto($block, $photo);
                break;

            case 'editPhoto':
                $title = dgettext('featuredphoto', 'Edit Photo');
                $content = FeaturedPhoto_Manager::editPhoto($block, $photo);
                break;

            case 'postPhoto':
                if (!FeaturedPhoto_Manager::postPhoto($photo))
                {
                    $title = dgettext('featuredphoto', 'Edit Photo');
                    $content = FeaturedPhoto_Manager::editPhoto($block, $photo, true);
                }
                else
                {
                    FeaturedPhoto_Manager::checkPermission('edit_photos', $photo);
                    $result = $photo->save();
                    if (PHPWS_Error::logIfError($result))
                    {
                        FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo could not be saved'),
                                                           array('action'=>'managePhotos',
                                                           'block_id'=>$photo->getBlockId()));
                    }
                    else
                    {
                        FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo saved'),
                                                           array('action'=>'managePhotos',
                                                           'block_id'=>$photo->getBlockId()));
                    }
                }
                break;

            case 'hidePhoto':
                $result = $photo->toggle();
                if (PHPWS_Error::logIfError($result))
                {
                    FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo activation could not be changed'),
                                                       array('action'=>'managePhotos',
                                                       'block_id'=>$photo->getBlockId()));
                }
                else
                {
                    FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo activation changed'),
                                                       array('action'=>'managePhotos',
                                                       'block_id'=>$photo->getBlockId()));
                }
                break;

            case 'deletePhoto':
                $photo->kill();
                FeaturedPhoto_Manager::sendMessage(dgettext('featuredphoto', 'Photo removed from photo block'),
                                                   array('action'=>'managePhotos',
                                                   'block_id'=>$photo->getBlockId()));
                break;

            case 'viewPhoto':
                $title = dgettext('featuredphoto', 'View Photo');
                $content = $photo->view();
                break;

            /******************** END PHOTO CASES *********************/
            /****************** BEGIN SETTINGS CASES ******************/

            case 'editSettings':
                $title = dgettext('featuredphoto', 'Settings');
                $content = FeaturedPhoto_Manager::editSettings();
                break;

            case 'postSettings':
                FeaturedPhoto_Manager::postSettings();
                break;

            /******************* END SETTINGS CASES *******************/
        }

        $template['TITLE'] = &$title;
        if (isset($message))
        {
            $template['MESSAGE'] = &$message;
        }
        $template['CONTENT'] = &$content;

        return PHPWS_Template::process($template, 'featuredphoto', 'admin.tpl');
    }

    function sendMessage($message, $command)
    {
        $_SESSION['featuredphoto_message'] = $message;
        if (is_array($command))
        {
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('featuredphoto', $command, true));
        }
        else
        {
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('featuredphoto', array('action'=>$command), true));
        }
    }

    function getMessage()
    {
        if (isset($_SESSION['featuredphoto_message']))
        {
            $message = $_SESSION['featuredphoto_message'];
            unset($_SESSION['featuredphoto_message']);
            return $message;
        }

        return NULL;
    }

    function checkPermission($permission, $item = NULL)
    {
        if (!Current_User::authorized('featuredphoto', $permission, (isset($item) ? $item->id : 0)))
        {
            Current_User::disallow();
            return false;
        }

        return true;
    }

    function listBlocks()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['TITLE']  = dgettext('featuredphoto', 'Title');
        $pageTags['MODE']   = dgettext('featuredphoto', 'Mode');
        $pageTags['ACTIVE'] = dgettext('featuredphoto', 'Active');
        $pageTags['ACTION'] = dgettext('featuredphoto', 'Action');
        $pager = new DBPager('featuredphoto_blocks', 'FeaturedPhoto_Block');
        $pager->setModule('featuredphoto');
        $pager->setTemplate('block/list.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($pageTags);
        $pager->addRowTags('getListTags');
        $pager->setSearch('title');
        $pager->setDefaultOrder('title', 'asc');
        $pager->setEmptyMessage(dgettext('featuredphoto', 'No photo blocks found.'));

        return $pager->get();
    }

    function editBlock(&$block, $js=false, $errors=NULL)
    {
        FeaturedPhoto_Manager::checkPermission('edit_blocks', $block);

        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form;
        $form->addHidden('module', 'featuredphoto');

        if ($js)
        {
            $form->addHidden('action', 'postJSBlock');
            if (isset($_REQUEST['key_id']))
            {
                $form->addHidden('key_id', (int)$_REQUEST['key_id']);
            }
            $form->addButton('cancel', dgettext('featuredphoto', 'Cancel'));
            $form->setExtra('cancel', 'onclick="window.close()"');
        }
        else
        {
            $form->addHidden('action', 'postBlock');
        }

        if (empty($block->id))
        {
            $form->addSubmit('submit', dgettext('featuredphoto', 'Save New Photo Block'));
        }
        else
        {
            $form->addHidden('block_id', $block->getId());
            $form->addSubmit('submit', dgettext('featuredphoto', 'Update Photo Block'));
        }

        $form->addText('title', $block->getTitle());
        $form->setLabel('title', dgettext('featuredphoto', 'Title'));
        $form->setSize('title', 50);

        $form->addRadioAssoc('mode', $block->allModes());
        $form->setMatch('mode', $block->getMode(false));
        $form->addTplTag('MODE_LABEL', dgettext('featuredphoto', 'Mode'));

        $db = new PHPWS_DB('featuredphoto_photos');
        $db->addWhere(array('active'=>1, 'block_id'=>$block->getId()));
        $db->addOrder('id');
        $photos = $db->select();
        if (!PHPWS_Error::logIfError($photos) && ($photos != NULL))
        {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

            foreach ($photos as $photo)
            {
                $image = Cabinet::getFile($photo['image_id']);
                $current_photos[$photo['id']] = $image->_source->title;
            }
        }
        else
        {
            $current_photos[0] = '(' . dgettext('featuredphoto', 'None') . ')';
        }
        $form->addSelect('current_photo', $current_photos);
        $form->setMatch('current_photo', $block->current_photo);

        if (PHPWS_Settings::get('featuredphoto', 'flickr_support'))
        {
            PHPWS_Core::initModClass('featuredphoto', 'lib/Flickr.php');
            $flickr_sets[0] = '(' . dgettext('featuredphoto', 'None') . ')';

            $f = new PHPWS_Flickr(PHPWS_Settings::get('featuredphoto', 'flickr_api_key'));
            $person = $f->people_findByUsername(PHPWS_Settings::get('featuredphoto', 'flickr_username'));
            if (!PHPWS_Error::logIfError($person))
            {
                $photosets = $f->photosets_getList($person['id']);
                if (!PHPWS_Error::logIfError($photosets))
                {
                    $flickr_sets = NULL;
                    foreach ($photosets as $photoset)
                    {
                        $flickr_sets[$photoset['id']] = $photoset['title'];
                    }
                }
            }

            $form->addSelect('flickr_set', $flickr_sets);
            $form->setMatch('flickr_set', $block->flickr_set);
            $form->setLabel('flickr_set', dgettext('featuredphoto', 'Flickr set to use'));
        }

        $form->addText('tn_width', $block->getTnWidth());
        $form->setSize('tn_width', 4);
        $form->addText('tn_height', $block->getTnHeight());
        $form->setSize('tn_height', 4);
        $form->addTplTag('RESIZE_LABEL', dgettext('featuredphoto', 'Resize images to fit inside'));
        $form->addTplTag('PIXELS_LABEL', dgettext('featuredphoto', 'pixels'));

        if ($template_list = PHPWS_Template::listTemplates('featuredphoto', 'block/boxstyles'))
        {
            $form->addSelect('template', $template_list);
            $form->setMatch('template', $block->getTemplate());
            $form->setLabel('template', dgettext('featuredphoto', 'Block Template'));
        }
        else
        {
            $form->addTplTag('TEMPLATE_LABEL', dgettext('featuredphoto', 'Block Template'));
            $form->addTplTag('TEMPLATE', dgettext('featuredphoto', 'Cannot locate any block templates. Cannot continue.'));
            $form->dropElement('submit');
        }

        $template = $form->getTemplate();
        if (isset($errors['title']))
        {
            $template['TITLE_ERROR'] = $errors['title'];
        }
        if (isset($errors['mode']))
        {
            $template['MODE_ERROR'] = $errors['mode'];
        }
        if (isset($errors['resize']))
        {
            $template['RESIZE_ERROR'] = $errors['resize'];
        }

        return PHPWS_Template::process($template, 'featuredphoto', 'block/edit.tpl');
    }

    function postBlock(&$block)
    {
        if (empty($_POST['title']))
        {
            $errors['title'] = dgettext('featuredphoto', 'Your photo block must have a title.');
        }
        if (($_POST['tn_width'] <= 0) || ($_POST['tn_height'] <= 0))
        {
            $errors['resize'] = dgettext('featuredphoto', 'Image dimensions must be greater than zero.');
        }
        if (($_POST['mode'] == FEATUREDPHOTO_BLOCK_MODE_FIXED) && ($_POST['current_photo'] == 0))
        {
            $errors['mode'] = dgettext('featuredphoto', 'Cannot choose Fixed mode with no image selected.');
        }
        if ((($_POST['mode'] == FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST_SET) ||
             ($_POST['mode'] == FEATUREDPHOTO_BLOCK_MODE_FLICKR_RANDOM_SET)) &&
            ($_POST['flickr_set'] == 0))
        {
            $errors['mode'] = dgettext('featuredphoto', 'Cannot choose Flickr Set mode with no set selected.');
        }

        $block->setTitle($_POST['title']);
        $block->setMode($_POST['mode']);
        $block->setCurrentPhoto($_POST['current_photo']);
        $block->setFlickrSet(isset($_POST['flickr_set']) ? $_POST['flickr_set'] : 0);
        $block->setTnWidth($_POST['tn_width']);
        $block->setTnHeight($_POST['tn_height']);
        $block->setTemplate($_POST['template']);

        if (isset($errors))
        {
            return $errors;
        }

        return true;
    }

    function pinBlockAll($block_id)
    {
        FeaturedPhoto_Manager::checkPermission('pin_blocks');

        $values['block_id'] = $block_id;
        $db = new PHPWS_DB('featuredphoto_pins');
        $db->addWhere($values);
        PHPWS_Error::logIfError($db->delete());
        $db->resetWhere();

        $values['key_id'] = -1;
        $db->addValue($values);

        PHPWS_Error::logIfError($db->insert());
    }

    function lockBlock($block_id, $key_id)
    {
        $block_id = (int)$block_id;
        $key_id = (int)$key_id;

        unset($_SESSION['Pinned_Photo_Blocks'][$block_id]);

        $values['block_id'] = $block_id;
        $values['key_id'] = $key_id;

        $db = new PHPWS_DB('featuredphoto_pins');
        $db->addWhere($values);
        $result = $db->delete();
        $db->addValue($values);
        return $db->insert();
    }

    function removeBlockPin()
    {
        FeaturedPhoto_Manager::checkPermission('pin_blocks');

        if (isset($_GET['block_id']))
        {
            $db = new PHPWS_DB('featuredphoto_pins');
            $db->addWhere('block_id', $_GET['block_id']);
            if (isset($_GET['key_id']))
            {
                $db->addWhere('key_id', $_GET['key_id']);
            }

            PHPWS_Error::logIfError($db->delete());
        }
    }

    function listPhotos($block_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        if (Current_User::allow('featuredphoto', 'edit_photos'))
        {
            $vars['action'] = 'newPhoto';
            $vars['block_id'] = $block_id;
            $pageTags['ADD_LINK'] = PHPWS_Text::secureLink(dgettext('featuredphoto', 'Add new photo'), 'featuredphoto', $vars);
        }
        $pageTags['NAME']    = dgettext('featuredphoto', 'Name');
        $pageTags['SIZE']    = dgettext('featuredphoto', 'Size');
        $pageTags['ACTIVE']  = dgettext('featuredphoto', 'Active');
        $pageTags['ACTION']  = dgettext('featuredphoto', 'Action');
        $pager = new DBPager('featuredphoto_photos', 'FeaturedPhoto');
        $pager->setModule('featuredphoto');
        $pager->setTemplate('photo/list.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($pageTags);
        $pager->addRowTags('getListTags');
        $pager->setDefaultOrder('id');
        $pager->addWhere('block_id', $block_id);
        $pager->setEmptyMessage(dgettext('featuredphoto', 'No photos found.'));

        return $pager->get();
    }

    function editPhoto(&$block, &$photo, $error=false)
    {
        FeaturedPhoto_Manager::checkPermission('edit_photos', $photo);

        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

        $form = new PHPWS_Form;
        $form->addHidden('module', 'featuredphoto');
        $form->addHidden('action', 'postPhoto');
        $form->addHidden('block_id', $photo->getBlockId());

        $manager = Cabinet::fileManager('image_id', $photo->getImageId());
        $manager->maxImageWidth($block->getTnWidth());
        $manager->maxImageHeight($block->getTnHeight());
        $manager->imageOnly(false, false);
        $form->addTplTag('IMAGE_MANAGER', $manager->get());

        if (empty($photo->id))
        {
            $form->addSubmit('submit', dgettext('featuredphoto', 'Save New Photo'));
        }
        else
        {
            $form->addHidden('photo_id', $photo->getId());
            $form->addSubmit('submit', dgettext('featuredphoto', 'Update Photo'));
        }

        $template = $form->getTemplate();
        if ($error)
        {
            $template['IMAGE_ERROR'] = dgettext('featuredphoto', 'You must select an image.');
        }

        return PHPWS_Template::process($template, 'featuredphoto', 'photo/edit.tpl');
    }

    function postPhoto(&$photo)
    {
        /* Verify image was selected. */
        if (!isset($_POST['image_id']) || ($_POST['image_id'] == 0))
        {
            return false;
        }

        $photo->setImageId($_POST['image_id']);
        return true;
    }

    function editSettings()
    {
        FeaturedPhoto_Manager::checkPermission('edit_settings');

        $form = new PHPWS_Form;
        $form->addHidden('module', 'featuredphoto');
        $form->addHidden('action', 'postSettings');

        $form->addCheck('flickr_support');
        $form->setMatch('flickr_support', PHPWS_Settings::get('featuredphoto', 'flickr_support'));
        $form->setLabel('flickr_support', dgettext('featuredphoto', 'Enable Flickr Support'));

        $form->addText('flickr_api_key', PHPWS_Settings::get('featuredphoto', 'flickr_api_key'));
        $form->setLabel('flickr_api_key', dgettext('featuredphoto', 'Flickr API Key'));
        $form->setSize('flickr_api_key', 50, 200);

        $form->addText('flickr_username', PHPWS_Settings::get('featuredphoto', 'flickr_username'));
        $form->setLabel('flickr_username', dgettext('featuredphoto', 'Flickr Username'));
        $form->setSize('flickr_username', 50, 200);

        $form->addSubmit('submit', dgettext('featuredphoto', 'Update Settings'));

        $template = $form->getTemplate();
        $template['FLICKR_NOTICE'] = dgettext('featuredphoto',
                                              'This module uses the Flickr API but is not endorsed or certified by Flickr.');

        return PHPWS_Template::process($template, 'featuredphoto', 'settings.tpl');
    }

    function postSettings()
    {
        FeaturedPhoto_Manager::checkPermission('edit_settings');

        $success_msg      = dgettext('featuredphoto', 'Your settings have been successfully saved.');
        $error_saving_msg = dgettext('featuredphoto', 'Error saving the settings. Check error log for details.');
        $error_inputs_msg = dgettext('featuredphoto', 'Please specify both API key and username.');
        $error_flickr_msg = dgettext('featuredphoto', 'Error from Flickr. Check error log for details.');
        $ret_msg          = &$success_msg;

        $api_key  = trim($_POST['flickr_api_key']);
        $username = trim($_POST['flickr_username']);

        PHPWS_Settings::set('featuredphoto', 'flickr_support',  0        );
        PHPWS_Settings::set('featuredphoto', 'flickr_api_key',  $api_key );
        PHPWS_Settings::set('featuredphoto', 'flickr_username', $username);

        if (isset($_POST['flickr_support']))
        {
            if (!empty($api_key) && !empty($username))
            {
                PHPWS_Core::initModClass('featuredphoto', 'lib/Flickr.php');

                $f = new PHPWS_Flickr($api_key);
                if (!PHPWS_Error::logIfError($f->people_findByUsername($username)))
                {
                    PHPWS_Settings::set('featuredphoto', 'flickr_support', 1);
                }
                else
                {
                    $ret_msg = &$error_flickr_msg;
                }
            }
            else
            {
                $ret_msg = &$error_inputs_msg;
            }
        }

        if (PHPWS_Error::logIfError(PHPWS_Settings::save('featuredphoto')))
        {
            $ret_msg = &$error_saving_msg;
        }

        /* If Flickr disabled, verify no block modes are set to Flickr. */
        if (!PHPWS_Settings::get('featuredphoto', 'flickr_support'))
        {
            $db = new PHPWS_DB('featuredphoto_blocks');
            $db->addWhere('mode', FEATUREDPHOTO_BLOCK_MODE_FLICKR_LATEST, '>=');
            $db->addValue('mode', FEATUREDPHOTO_BLOCK_MODE_INCREMENT);
            PHPWS_Error::logIfError($db->update());
        }

        FeaturedPhoto_Manager::sendMessage($ret_msg, 'editSettings');
    }

}// END CLASS FeaturedPhoto_Manager

?>