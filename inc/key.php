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
 * @version     $Id: key.php,v 1.4 2007/09/30 22:48:49 blindman1344 Exp $
 */

function featuredphoto_unregister_key(&$key)
{
    if (empty($key) || empty($key->id))
    {
        return FALSE;
    }

    $db = new PHPWS_DB('featuredphoto_pins');
    $db->addWhere('key_id', $key->id);
    $result = $db->delete();
    PHPWS_Error::logIfError($result);
}

?>