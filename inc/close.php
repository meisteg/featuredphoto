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
 * @version     $Id: close.php,v 1.5 2008/02/22 04:06:19 blindman1344 Exp $
 */

FeaturedPhoto_Runtime::show();

if (Current_User::allow('featuredphoto', 'edit_blocks'))
{
    $key = Key::getCurrent();
    if (Key::checkKey($key) && javascriptEnabled())
    {
        $val['address'] = PHPWS_Text::linkAddress('featuredphoto', array('action'=>'editJSBlock', 'key_id'=>$key->id), true);
        $val['label'] = dgettext('featuredphoto', 'Add photo block here');
        $val['width'] = 640;
        $val['height'] = 480;

        MiniAdmin::add('featuredphoto', javascript('open_window', $val));
    }
}

?>