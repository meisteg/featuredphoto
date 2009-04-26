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
 * @version     $Id: featuredphoto.php,v 1.9 2006/03/06 03:05:37 blindman1344 Exp $
 */

class PHPWS_featuredphoto {

    var $id = NULL;
    var $name = NULL;
    var $caption = NULL;
    var $credit = NULL;
    var $size = NULL;
    var $type = NULL;
    var $filename = NULL;
    var $width = NULL;
    var $height = NULL;
    var $hidden = 0;
    var $rec_date = '0000-00-00';

    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function PHPWS_featuredphoto($ID = NULL) {
        if(isset($ID) && (is_array($ID))) {
            $this->load_item($ID['id']);
        }
        else if(isset($ID)) {
            $this->load_item($ID);
        }
    }// END FUNC PHPWS_featuredphoto

    /**
     * Form to add/edit featured photo
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function edit() {
        if(!$_SESSION['OBJ_user']->allow_access('featuredphoto','add_photos') && !$_SESSION['OBJ_user']->allow_access('featuredphoto','edit_photos')) {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it("You don't have permission to view this page");
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::edit()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php';
        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';
        require_once PHPWS_SOURCE_DIR . 'core/WizardBag.php';

        $tabs = 1;
        $tags = array();
        $form = new EZForm('featuredphoto_edit');

        $form->add('NAME', 'text', $this->name);
        $form->setSize('NAME', 50);
        $form->setMaxSize('NAME', 100);
        $form->setTab('NAME', $tabs);
        $tabs++;

        $form->add('CAPTION', 'textarea', $this->caption);
        $form->setId('CAPTION');
        $form->setRows('CAPTION', 10);
        $form->setCols('CAPTION', 70);
        $form->setTab('CAPTION', $tabs);
        $tabs++;

        $form->add('CREDIT', 'text', $this->credit);
        $form->setSize('CREDIT', 50);
        $form->setTab('CREDIT', $tabs);
        $tabs++;

        if(isset($this->id)) {
            $tags['TITLE'] = $_SESSION['translate']->it('Edit Photo');
            $form->add('photo_id', 'hidden', $this->id);
        }
        else {
            $form->add('FILE', 'file');
            $form->setTab('FILE', $tabs);
            $tabs++;

            $tags['FILE_LABEL'] = $_SESSION['translate']->it('File');
            $tags['TITLE'] = $_SESSION['translate']->it('Add New Photo');
        }

        $form->add('SAVE_BUTTON', 'submit', $_SESSION['translate']->it('Save Photo'));
        $form->setTab('SAVE_BUTTON', $tabs);

        $form->add('module', 'hidden', 'featuredphoto');
        $form->add('photo_op', 'hidden', 'save');

        $tags = array_merge($tags, $form->getTemplate());
        $tags['CAPTION_WYSIWYG'] = PHPWS_WizardBag::js_insert('wysiwyg', 'featuredphoto_edit', 'CAPTION');
        $tags['NAME_LABEL'] = $_SESSION['translate']->it('Name');
        $tags['CAPTION_LABEL'] = $_SESSION['translate']->it('Caption');
        $tags['CREDIT_LABEL'] = $_SESSION['translate']->it('Photo Credit');
        $tags['SIZE_LABEL'] = $_SESSION['translate']->it('Size');
        $tags['TYPE_LABEL'] = $_SESSION['translate']->it('Type');

        $tags['CREDIT_HELP'] = CLS_help::show_link('featuredphoto', 'photo_credit');

        $GLOBALS['CNT_featuredphoto']['content'] .= PHPWS_Template::processTemplate($tags, 'featuredphoto', 'edit.tpl');
    }// END FUNC edit

    /**
     * Loads a featured photo
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function load_item($ID) {
        $result = $GLOBALS['core']->sqlSelect('mod_featuredphoto_photos', 'id', $ID);
        if($result != NULL) {
            $classVars = get_class_vars(get_class($this));

            foreach($result[0] as $key => $value) {
                if(array_key_exists($key, $classVars)) {
                    $this->{$key} = $value;
                }
            }
        }
        else {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it('Photo ID ([var1]) was not found in database', $ID);
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::load_item()', $message, 'exit');
            $error->message();
        }
    }// END FUNC load_item

    /**
     * Save a photo to the database and file system
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function save() {
        if(!$_SESSION['OBJ_user']->allow_access('featuredphoto','add_photos') && !$_SESSION['OBJ_user']->allow_access('featuredphoto','edit_photos')) {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it("You don't have permission to view this page");
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        // This will prevent overwriting other entries if user has multiple tabs/windows open
        if (isset($_POST['photo_id'])) {
            $this->load_item($_POST['photo_id']);
        }
        else {
            $this->id = NULL;
        }

        if($this->id) {
        }
        else if($_FILES['FILE']['error'] == 0) {
            $name = str_replace(' ', '_', $_FILES['FILE']['name']);
            $file = $GLOBALS['core']->home_dir . 'images/featuredphoto/' . $name;

            if(is_file($file)) {
                $name = time() . '_' . str_replace(' ', '_', $_FILES['FILE']['name']);
                $file = $GLOBALS['core']->home_dir . 'images/featuredphoto/' . $name;
            }

            @move_uploaded_file($_FILES['FILE']['tmp_name'], $file);
            if(is_file($file)) {
                chmod($file, 0664);
                $info = @getimagesize($file);

                include(PHPWS_SOURCE_DIR.'conf/allowedImageTypes.php');

                if(in_array($_FILES['FILE']['type'], $allowedImageTypes)) {
                    $this->filename = $name;
                    $this->type = $_FILES['FILE']['type'];
                    $this->size = $_FILES['FILE']['size'];
                    $this->width = $info[0];
                    $this->height = $info[1];

                    // Check to see if we need to resize smaller
                    if (function_exists('imagecreate') && (($this->width > $_SESSION['FEATUREDPHOTO_Manager']->settings['resize_width']) ||
                        ($this->height > $_SESSION['FEATUREDPHOTO_Manager']->settings['resize_height']))) {
                        require_once(PHPWS_SOURCE_DIR . 'core/File.php');
                        $newImage = PHPWS_File::makeThumbnail($this->filename,
                                        $GLOBALS['core']->home_dir . 'images/featuredphoto/',
                                        $GLOBALS['core']->home_dir . 'images/featuredphoto/',
                                        $_SESSION['FEATUREDPHOTO_Manager']->settings['resize_width'],
                                        $_SESSION['FEATUREDPHOTO_Manager']->settings['resize_height'],
                                        TRUE);
                        $this->filename = $newImage[0];
                        $this->size = filesize($GLOBALS['core']->home_dir . 'images/featuredphoto/' . $this->filename);
                        $newImageInfo = getimagesize($GLOBALS['core']->home_dir . 'images/featuredphoto/' . $this->filename);
                        $this->width = $newImageInfo[0];
                        $this->height = $newImageInfo[1];
                        if($newImageInfo[2] == 2) {
                            $this->type = 'image/jpeg';
                        }
                    }
                } else {
                    @unlink($file);
                    require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
                    $message = $_SESSION['translate']->it('The image uploaded was not an allowed image type.');
                    $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
                    $error->message('CNT_featuredphoto');
                    return FALSE;
                }
            } else {
                require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
                $message = $_SESSION['translate']->it('There was a problem uploading the specified image.');
                $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
                $error->message('CNT_featuredphoto');
                return FALSE;
            }
        } else if($_FILES['FILE']['error'] != 4) {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it('The file uploaded exceeded the max size allowed.');
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        } else {
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it('You need to specify a file to upload.');
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        if(strlen($_POST['NAME']) > 0) {
            $this->name = PHPWS_Text::parseInput($_POST['NAME']);
        }
        else {
            @unlink($file);
            require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
            $message = $_SESSION['translate']->it('A photo name is required.');
            $error = new PHPWS_Error('featuredphoto', 'PHPWS_featuredphoto::save()', $message);
            $error->message('CNT_featuredphoto');
            return FALSE;
        }

        $this->caption = PHPWS_Text::parseInput($_POST['CAPTION']);

        if(strlen($_POST['CREDIT']) > 0) {
            $this->credit = PHPWS_Text::parseInput($_POST['CREDIT']);
        }
        else {
            $this->credit = NULL;
        }

        $this->commit();
        $_SESSION['FEATUREDPHOTO_Manager']->message = $_SESSION['translate']->it('Photo Saved Successfully');
        $_REQUEST['man_op'] = 'list';
        $_SESSION['FEATUREDPHOTO_Manager']->action();
    }// END FUNC save

    /**
     * Commit a featured photo object to the database
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function commit() {
        $this->rec_date = time();

        $commitValues = get_object_vars($this);
        unset($commitValues['id']);

        if(isset($this->id)) {
            return $GLOBALS['core']->sqlUpdate($commitValues, 'mod_featuredphoto_photos', 'id', $this->id);
        }
        else {
            return ($this->id = $GLOBALS['core']->sqlInsert($commitValues, 'mod_featuredphoto_photos', FALSE, TRUE, FALSE));
        }
    }// END FUNC commit

    /**
     * Views a photo
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function view() {
        $tags['NAME'] = PHPWS_Text::parseOutput($this->name);
        $tags['CAPTION'] = PHPWS_Text::parseOutput($this->caption);
        if($this->credit != NULL) $tags['CREDIT'] = PHPWS_Text::parseOutput($this->credit);
        $tags['WIDTH'] = $this->width;
        $tags['HEIGHT'] = $this->height;
        $tags['FILENAME'] = 'http://' . PHPWS_HOME_HTTP . 'images/featuredphoto/' . $this->filename;

        return PHPWS_Template::processTemplate($tags, 'featuredphoto', 'view.tpl');
    }// END FUNC view

    /**
     * Remove a photo from the database and file system
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function kill() {
        if($_SESSION['OBJ_user']->allow_access('featuredphoto','delete_photos')) {
            $file = $GLOBALS['core']->home_dir . 'images/featuredphoto/' . $this->filename;
            @unlink($file);
            return $GLOBALS['core']->sqlDelete('mod_featuredphoto_photos', array('id'=>$this->id));
        }
    }

    /**
     * Toggles whether this photo is hidden
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function toggleHidden() {
        if($_SESSION['OBJ_user']->allow_access('featuredphoto','show_hide_photos')) {
            if($this->hidden) {
                $this->hidden = 0;
            }
            else {
                $this->hidden = 1;
            }
        }
    }// END FUNC toggleHidden

    /**
     * Get the photo name for photo lists
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListName() {
        return '<a href="./index.php?module=featuredphoto&amp;man_op=viewPhoto&amp;id=' . $this->id . '">' . $this->name . '</a>';
    }// END FUNC getListName

    /**
     * Get the photo size for photo lists
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListSize() {
        if($this->size < 1024) {
            // Display in bytes
            return number_format($this->size, 2) . ' bytes';

        } else if($this->size < pow(2, 20)) {
            // Display in kilobytes
            return number_format(round(($this->size/1024),2), 2) . ' KB';

        } else {
            // Display in megabytes
            return number_format(round(($this->size/1024)/1024,2), 2) . ' MB';
        }
    }// END FUNC getListSize

    /**
     * Get the photo date for photo lists
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListRec_date() {
        return date(PHPWS_DATE_FORMAT, $this->rec_date);
    }// END FUNC getListRec_date

    /**
     * Get photo actions for photo lists
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListActions() {
        $showText = $_SESSION['translate']->it('Show');
        $hideText = $_SESSION['translate']->it('Hide');
        $viewText = $_SESSION['translate']->it('View');
        $editText = $_SESSION['translate']->it('Edit');
        $deleteText = $_SESSION['translate']->it('Delete');

        $actions = array();

        if($_SESSION['OBJ_user']->allow_access('featuredphoto','show_hide_photos')) {
            if($this->hidden) {
                $actions[] = '<a href="./index.php?module=featuredphoto&amp;man_op=toggle&amp;id=' . $this->id . '">' . $showText . '</a>';
            } else {
                $actions[] = '<a href="./index.php?module=featuredphoto&amp;man_op=toggle&amp;id=' . $this->id . '">' . $hideText . '</a>';
            }
        }

        $actions[] = '<a href="./index.php?module=featuredphoto&amp;man_op=viewPhoto&amp;id=' . $this->id . '">' . $viewText . '</a>';

        if($_SESSION['OBJ_user']->allow_access('featuredphoto','edit_photos')) {
            $actions[] = '<a href="./index.php?module=featuredphoto&amp;man_op=editPhoto&amp;id=' . $this->id . '">' . $editText . '</a>';
        }

        if($_SESSION['OBJ_user']->allow_access('featuredphoto','delete_photos')) {
            if(($_SESSION['FEATUREDPHOTO_Manager']->settings['current'] != $this->id) || ($_SESSION['FEATUREDPHOTO_Manager']->settings['mode'] != 2)) {
                $actions[] = '<a href="./index.php?module=featuredphoto&amp;man_op=deletePhoto&amp;id=' . $this->id . '">' . $deleteText . '</a>';
            }
        }

        return implode(' | ', $actions);
    }// END FUNC getListActions

    /**
     * Performs actions for selected operation
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action() {
        switch($_REQUEST['photo_op']) {
            case 'add':
            $this->edit();
            break;

            case 'edit':
            $this->edit();
            break;

            case 'save':
            $this->save();
            break;

            case 'view':
            $GLOBALS['CNT_featuredphoto']['content'] .= $this->view();
            break;
        }
    }// END FUNC action

}// END CLASS PHPWS_featuredphoto

?>