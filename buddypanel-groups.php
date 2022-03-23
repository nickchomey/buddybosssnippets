<?php
/**
 * Add User's Groups to BuddyPanel
 */

toolset_snippet_security_check() or die( 'Direct access is not allowed' );

// Put the code of your snippet below this comment.

function tabi_custom_nav_menu_items($items, $menu)
{
 //BuddPress sidebar menu
    if ($menu->slug == 'side-menu' && !is_admin()) {
        $user_id = get_current_user_id();
        $parent_id = 0;
        $group_ids = groups_get_user_groups($user_id);
        foreach ($items as $item) {
            if ('My Groups' == $item->title) {
                $parent_id = $item->ID;
            }
        }  
        $order_index = count($items);
        $user_groups = [];
        if ($group_ids['total']) {
            foreach ($group_ids['groups'] as $i => $group_id) {
                $group = groups_get_group($group_id);
                $link = bp_get_group_permalink($group);
                $order_index++;
                $user_groups[] = [
                'name' => $group->name,
                'link' => $link,
                'order_index' => $order_index,
                'parent_id' => $parent_id,
                'group_id' => $group_id
                ];
            }
            array_multisort($user_groups);
            foreach ($user_groups as $user_group){
                $items[] = tabi_custom_nav_menu_item($user_group['group_id'], $user_group['name'], $user_group['link'], $user_group['order_index'], $user_group['parent_id']);
            }                            
        } 
        else {
            $order_index++;
            $items[] = tabi_custom_nav_menu_item('Groups you join will be listed here', get_site_url('/#'), $order_index, $parent_id);
        }
    }
    return $items;
}
add_filter('wp_get_nav_menu_items', 'tabi_custom_nav_menu_items', 20, 2);
/**
 * Simple helper function for make menu item objects
 * 
 * @param $title      - menu item title
 * @param $url        - menu item url
 * @param $order      - where the item should appear in the menu
 * @param int $parent - the item's parent item
 * @return \stdClass
 */
function tabi_custom_nav_menu_item($group_id, $name, $url, $order, $parent = 0)
{
    $item = new stdClass();
    $item->group_id = $group_id;    
    $item->ID = 1000000 + $order + $parent;
    $item->db_id = $item->ID;
    $item->title = $name;
    $item->name = $name;
    $item->url = $url;
    $item->menu_order = $order;
    $item->menu_item_parent = $parent;
    $item->type = '';
    $item->object = '';
    $item->object_id = '';
    $item->classes = array();
    $item->target = '';
    $item->attr_title = '';
    $item->description = '';
    $item->xfn = '';
    $item->status = '';
    return $item;
}

add_filter ('nav_menu_item_title', 'replace_buddypanel_groups_icons', 10, 4);
function replace_buddypanel_groups_icons ($title, $item, $args, $depth){
    global $groups_template;
    
    if (isset($item->group_id)){
        
        $avatar = bp_core_fetch_avatar(
            array(
                'item_id'    => $item->group_id,
                'avatar_dir' => 'group-avatars',
                'object'     => 'group',
                'type'       => 'thumb',
                'alt'        => sprintf( __( 'Group logo of %s', 'buddyboss' ), $item->name ),
                'css_id'     => false,
                'class'      => 'avatar',
                'width'      => 25,
                'height'     => 25,
            )
        );
    
        // If No avatar found, provide some backwards compatibility.
        if ( empty( $avatar ) ) {
            
            $avatar = "<i class='_mi _before buddyboss bb-icon-groups' aria-hidden='true'></i>";
            //$avatar = '<img src="' . esc_url( $groups_template->group->avatar_thumb ) . '" class="avatar" alt="' . esc_attr( $groups_template->group->name ) . '" />';
        }
        
        $avatar .= "<span style='margin-left: 15px;'>{$item->name}</span>";
        return $avatar;
    }
    return $title;
}
