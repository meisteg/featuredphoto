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
 * @version     $Id: update.php,v 1.20 2008/03/30 14:18:12 blindman1344 Exp $
 */

function featuredphoto_update(&$content, $currentVersion)
{
    switch ($currentVersion)
    {
        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '- This package will not update versions prior to 1.1.0.';
            return false;

        case version_compare($currentVersion, '1.2.0', '<'):
            $db = new PHPWS_DB('featuredphoto_blocks');

            /* First, need to add new column to block table. */
            if (PHPWS_Error::logIfError($db->addTableColumn('flickr_set', 'BIGINT unsigned NOT NULL', 'current_photo')))
            {
                $content[] = '- Unable to create table column flickr_set in featuredphoto_blocks table.';
                return false;
            }

            /* Update permissions table. */
            Users_Permission::registerPermissions('featuredphoto', $content);

            /* Update the templates and config file. */
            $files = array('templates/settings.tpl', 'templates/block/edit.tpl',
                           'templates/photo/flickr_view.tpl', 'conf/error.php');
            featuredphoto_update_files($files, $content);

            $content[] = '- Added ability to pin a photo block to all pages.';
            $content[] = '- Added Flickr support.';
            $content[] = '- Added German translation.';

        case version_compare($currentVersion, '1.2.1', '<'):
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            if (!Cabinet::convertImagesToFileAssoc('featuredphoto_photos', 'image_id'))
            {
                $content[] = '- Could not convert images to new File Cabinet format.';
                return false;
            }

            /* Update the templates and config file. */
            $files = array('templates/photo/view.tpl', 'templates/photo/edit.tpl', 'conf/config.php');
            featuredphoto_update_files($files, $content);

            $content[] = '- Support File Cabinet 2.0.';
            $content[] = '- Corrected a few phrases that were not being translated.';
    }

    return true;
}

function featuredphoto_update_files($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'featuredphoto'))
    {
        $content[] = '- Updated the following files:';
    }
    else
    {
        $content[] = '- Unable to update the following files:';
    }

    foreach ($files as $file)
    {
        $content[] = '--- ' . $file;
    }
}

?>