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
 * @version     $Id: uninstall.php,v 1.5 2007/05/28 20:00:43 blindman1344 Exp $
 */

function featuredphoto_uninstall(&$content)
{
    PHPWS_DB::dropTable('featuredphoto_photos');
    PHPWS_DB::dropTable('featuredphoto_blocks');
    PHPWS_DB::dropTable('featuredphoto_pins');
    $content[] = dgettext('featuredphoto', 'Featured Photo tables removed.');
    return TRUE;
}

?>