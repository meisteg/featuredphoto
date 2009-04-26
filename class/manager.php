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
 * @version     $Id: manager.php,v 1.7 2006/03/06 03:05:37 blindman1344 Exp $
 */

require_once(PHPWS_SOURCE_DIR . 'core/List.php');
require_once(PHPWS_SOURCE_DIR . 'mod/featuredphoto/class/featuredphoto.php');

class FEATUREDPHOTO_Manager {

    var $photo = NULL;
    var $photoList = NULL;
    var $settings = array();
    var $message = NULL;

    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function FEATUREDPHOTO_Manager() {
        $sql = 'SELECT * FROM mod_featuredphoto_settings';
        $this->settings = $GLOBALS['core']->quickFetch($sql, TRUE);
    }// END FUNC FEATUREDPHOTO_Manager

    /**
     * Creates the menu
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _menu() {
        $settingsText = $_SESSION['translate']->it('Settings');
        $newText = $_SESSION['translate']->it('New Photo');
        $listText = $_SESSION['translate']->it('List Photos');

        $links = array();

        if($_SESSION['OBJ_user']->allow_access('featuredphoto','add_photos')) {
            $links[] = '<a href="./index.php?module=featuredphoto&amp;man_op=newPhoto">' . $newText . '</a>';
        }

        $links[] = '<a href="./index.php?module=featuredphoto&amp;man_op=list">' . $listText . '</a>';

        if($_SESSION['OBJ_user']->allow_access('featuredphoto','edit_settings')) {
            $links[] = '<a href="./index.php?module=featuredphoto&amp;man_op=settings">' . $settingsText . '</a>';
        }

        $tags = array();
        $tags['LINKS'] = implode(' | ', $links);

        $GLOBALS['CNT_featuredphoto']['content'] = PHPWS_Template::processTemplate($tags, 'featuredphoto', 'menu.tpl');
    }// END FUNC _menu

    /**
     * Lists the photos in the database
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _list() {
        if(!$_SESSION['OBJ_user']->allow_access('featuredphoto')) {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it("You don't have permission to view this page");
            $error = new PHPWS_Error('featuredphoto', 'FEATUREDPHOTO_Manager::_list()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        $this->_menu();

        $tags = array();
        if(isset($this->message)) {
            $tags['MESSAGE'] = $this->message;
            $this->message = NULL;
        }

        $listTags = array();
        $listTags['TITLE'] = $_SESSION['translate']->it('Current Photos');
        $listTags['NAME_LABEL'] = $_SESSION['translate']->it('Name');
        $listTags['SIZE_LABEL'] = $_SESSION['translate']->it('Size');
        $listTags['REC_DATE_LABEL'] = $_SESSION['translate']->it('Updated');
        $listTags['ACTIONS_LABEL'] = $_SESSION['translate']->it('Actions');

        if(!isset($this->photoList)) {
            $this->photoList = new PHPWS_List;
        }

        $this->photoList->setModule('featuredphoto');
        $this->photoList->setClass('PHPWS_featuredphoto');
        $this->photoList->setTable('mod_featuredphoto_photos');
        $this->photoList->setDbColumns(array('id', 'name', 'size', 'rec_date'));
        $this->photoList->setListColumns(array('name', 'size', 'rec_date', 'actions'));
        $this->photoList->setName('photos');
        $this->photoList->setTemplate('list');
        $this->photoList->setOp('man_op=list');
        $this->photoList->setPaging(array('limit'=>10, 'section'=>TRUE, 'limits'=>array(5,10,20,50), 'back'=>'&#60;&#60;', 'forward'=>'&#62;&#62;', 'anchor'=>FALSE));
        $this->photoList->setExtraListTags($listTags);

        $tags['PHOTOS'] = $this->photoList->getList();

        $GLOBALS['CNT_featuredphoto']['content'] .= PHPWS_Template::processTemplate($tags, 'featuredphoto', 'photos.tpl');
    }// END FUNC _list

    /**
     * Shows a featured photo (only if the block is enabled)
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function view() {
        if($this->settings['showblock']) {
            $GLOBALS['CNT_featuredphoto_pub']['title'] = $this->settings['blocktitle'];

            if($this->settings['mode'] == 1) {
                $sql = 'SELECT id FROM mod_featuredphoto_photos WHERE hidden=0 ORDER BY RAND() LIMIT 1';
                $id = $GLOBALS['core']->getOne($sql, TRUE);
            }
            else if($this->settings['mode'] == 2) {
                $sql = "SELECT id FROM mod_featuredphoto_photos WHERE hidden='0' AND id='" . $this->settings['current'] . "' LIMIT 1";
                $id = $GLOBALS['core']->getOne($sql, TRUE);
            }
            else {
                $result = $GLOBALS['core']->sqlSelect('mod_featuredphoto_photos', 'hidden', 0, 'id');

                $newFound = FALSE;
                if($result != NULL) {
                    foreach ($result as $row) {
                        if (($row['id'] > $this->settings['current']) && (!$newFound)) {
                            $newFound = TRUE;
                            $this->settings['current'] = $row['id'];
                        }
                    }
                }

                if (!$newFound) {
                    $result = $GLOBALS['core']->sqlSelect('mod_featuredphoto_photos', 'hidden', 0, 'id', NULL, NULL, 1);

                    if($result != NULL) {
                        $newFound = TRUE;
                        $this->settings['current'] = $result[0]['id'];
                    }
                }

                if($newFound) {
                    $GLOBALS['core']->sqlUpdate($this->settings, 'mod_featuredphoto_settings');
                    $id = $this->settings['current'];
                }
            }

            if(isset($id)) {
                $this->photo = new PHPWS_featuredphoto($id);
                $GLOBALS['CNT_featuredphoto_pub']['content'] = $this->photo->view();
            }
            else {
                $GLOBALS['CNT_featuredphoto_pub']['content'] = $_SESSION['translate']->it('No active photo');
            }
        }
    }// END FUNC view

    /**
     * Edit/save module settings
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _settings() {
        if(!$_SESSION['OBJ_user']->allow_access('featuredphoto','edit_settings')) {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it("You don't have permission to view this page");
            $error = new PHPWS_Error('featuredphoto', 'FEATUREDPHOTO_Manager::_settings()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';
        require_once PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php';

        if(isset($_POST['man_op']) && ($_POST['man_op'] == 'save')) {
            if(isset($_POST['MODE'])) {
                $this->settings['mode'] = $_POST['MODE'];
            }
            if(isset($_POST['SHOWBLOCK'])) {
                $this->settings['showblock'] = 1;
            } else {
                $this->settings['showblock'] = 0;
            }
            if(isset($_POST['BLOCKTITLE'])) {
                $this->settings['blocktitle'] = $_POST['BLOCKTITLE'];
            }
            if(isset($_POST['CURRENT']) && ($this->settings['mode'] == 2)) {
                $this->settings['current'] = $_POST['CURRENT'];
            }
            if(isset($_POST['RESIZE_WIDTH']) && is_numeric($_POST['RESIZE_WIDTH']) && ($_POST['RESIZE_WIDTH'] > 0)) {
                $this->settings['resize_width'] = $_POST['RESIZE_WIDTH'];
            }
            if(isset($_POST['RESIZE_HEIGHT']) && is_numeric($_POST['RESIZE_HEIGHT']) && ($_POST['RESIZE_HEIGHT'] > 0)) {
                $this->settings['resize_height'] = $_POST['RESIZE_HEIGHT'];
            }
        }

        $tabs = 1;
        $form = new EZform('featuredphoto_settings');
        $form->add('module', 'hidden', 'featuredphoto');
        $form->add('man_op', 'hidden', 'save');

        $form->add('SHOWBLOCK', 'checkbox', 1);
        $form->setMatch('SHOWBLOCK', $this->settings['showblock']);
        $form->setTab('SHOWBLOCK', $tabs);
        $tabs++;

        $form->add('BLOCKTITLE', 'text', $this->settings['blocktitle']);
        $form->setSize('BLOCKTITLE', '35');
        $form->setMaxSize('BLOCKTITLE', '100');
        $form->setTab('BLOCKTITLE', $tabs);
        $tabs++;

        $form->add('MODE', 'radio', array(0=>0,1=>1,2=>2));
        $form->setMatch('MODE', $this->settings['mode']);
        $form->setTab('MODE', $tabs);
        $tabs++;

        $options = array();
        $result = $GLOBALS['core']->sqlSelect('mod_featuredphoto_photos', 'hidden', 0, 'name');
        if($result != NULL) {
            foreach ($result as $row) {
                $options[$row['id']] = $row['name'];
            }
        }

        $form->add('CURRENT', 'select', $options);
        $form->setMatch('CURRENT', $this->settings['current']);
        $form->setTab('CURRENT', $tabs);
        $tabs++;

        $form->add('RESIZE_WIDTH', 'text', $this->settings['resize_width']);
        $form->setSize('RESIZE_WIDTH', '4');
        $form->setMaxSize('RESIZE_WIDTH', '4');
        $form->setTab('RESIZE_WIDTH', $tabs);
        $tabs++;

        $form->add('RESIZE_HEIGHT', 'text', $this->settings['resize_height']);
        $form->setSize('RESIZE_HEIGHT', '4');
        $form->setMaxSize('RESIZE_HEIGHT', '4');
        $form->setTab('RESIZE_HEIGHT', $tabs);
        $tabs++;

        $form->add('SAVE_BUTTON', 'submit', $_SESSION['translate']->it('Save Settings'));
        $form->setTab('SAVE_BUTTON', $tabs);

        $tags = array();
        $tags = $form->getTemplate();

        if(isset($_POST['man_op']) && ($_POST['man_op'] == 'save')) {
            if($GLOBALS['core']->sqlUpdate($this->settings, 'mod_featuredphoto_settings')) {
                $tags['MESSAGE'] = $_SESSION['translate']->it('Settings saved successfully');
            } else {
                $tags['MESSAGE'] = $_SESSION['translate']->it('There was a problem saving to the database');
            }
        }

        $tags['SHOWBLOCK_LABEL'] = $_SESSION['translate']->it('Show block on homepage');
        $tags['BLOCKTITLE_LABEL'] = $_SESSION['translate']->it('Homepage block title');
        $tags['MODE_LABEL'] = $_SESSION['translate']->it('Block mode');
        $tags['MODE_1_LABEL'] = $_SESSION['translate']->it('Increment');
        $tags['MODE_2_LABEL'] = $_SESSION['translate']->it('Random');
        $tags['MODE_3_LABEL'] = $_SESSION['translate']->it('Fixed on the image');
        $tags['RESIZE_LABEL'] = $_SESSION['translate']->it('Resize images to fit inside');
        $tags['PIXELS_LABEL'] = $_SESSION['translate']->it('pixels');
        if(!function_exists('imagecreate')) $tags['GD_LABEL'] = $_SESSION['translate']->it('Requires GD library');

        $tags['MODE_HELP'] = CLS_help::show_link('featuredphoto', 'mode');
        $tags['RESIZE_HELP'] = CLS_help::show_link('featuredphoto', 'resize');

        $GLOBALS['CNT_featuredphoto']['content'] .= PHPWS_Template::processTemplate($tags, 'featuredphoto', 'settings.tpl');
    }// END FUNC _settings

    /**
     * Performs actions based on operation requested
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action() {
        switch($_REQUEST['man_op']) {
            case 'list':
            $this->_list();
            break;

            case 'newPhoto':
            $this->_menu();
            $this->photo = new PHPWS_featuredphoto;
            $_REQUEST['photo_op'] = 'add';
            break;

            case 'editPhoto':
            $this->_menu();
            $this->photo = new PHPWS_featuredphoto($_REQUEST['id']);
            $_REQUEST['photo_op'] = 'edit';
            break;

            case 'toggle':
            $this->_menu();
            $this->photo = new PHPWS_featuredphoto($_REQUEST['id']);
            $this->photo->toggleHidden();
            $this->photo->commit();
            $this->_list();
            break;

            case 'deletePhoto':
            $this->_menu();
            $this->photo = new PHPWS_featuredphoto($_REQUEST['id']);
            $this->photo->kill();
            $this->photo = NULL;
            $this->message = $_SESSION['translate']->it('Photo Deleted');
            $this->_list();
            break;

            case 'viewPhoto':
            $this->_menu();
            $this->photo = new PHPWS_featuredphoto($_REQUEST['id']);
            $_REQUEST['photo_op'] = 'view';
            break;

            case 'settings':
            case 'save':
            $this->_menu();
            $this->_settings();
            break;
        }
    }// END FUNC action

}// END CLASS FEATUREDPHOTO_Manager

?>