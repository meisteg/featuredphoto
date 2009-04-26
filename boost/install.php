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
 * @version     $Id: install.php,v 1.5 2006/03/06 00:31:37 blindman1344 Exp $
 */

/* Make sure the user is a deity before running this script */
if (!$_SESSION['OBJ_user']->isDeity()){
    header('location:index.php');
    exit();
}

if (version_compare($GLOBALS['core']->version, '0.10.1') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.10.1 or greater to install.<br />';
    $content .= 'You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

require_once(PHPWS_SOURCE_DIR . 'core/File.php');

/* Import installation database and dump result into status variable */
if($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/featuredphoto/boost/install.sql', TRUE)) {
    $content .= 'All Featured Photo tables successfully written.<br /><br />';

    /* Check for permissions and create images directory if possible */
    if (is_writable($GLOBALS['core']->home_dir . 'images/')) {
        if(!is_dir($GLOBALS['core']->home_dir . 'images/featuredphoto')) {
            PHPWS_File::makeDir($GLOBALS['core']->home_dir . 'images/featuredphoto');
            if(is_dir($GLOBALS['core']->home_dir . 'images/featuredphoto')) {
               $content .= 'Images directory successfully created in:<br />' . $GLOBALS['core']->home_dir .
                           'images/featuredphoto<br /><br />';
            } else {
               $content .= 'Boost could not create the featuredphoto images directory in:<br />' . $GLOBALS['core']->home_dir .
                           'images/featuredphoto<br />You will have to do this manually!<br /><br />';
            }
        }
    } else {
        $content .= 'Images directory is not writable.  Please check the permissions and re-install.<br /><br />';
    }
} else {
    $content .= 'There was a problem writing to the database!<br /><br />';
}

?>