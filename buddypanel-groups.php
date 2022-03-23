<?php
function tabi_custom_nav_menu_items($items, $menu) {
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
            //extract name column of user_groups array
            $user_groups_lower = array_map('strtolower', array_column($user_groups,'name'));
            //sort user_groups by lower_case column, for case-insensitive sort
            array_multisort($user_groups_lower, SORT_ASC, $user_groups );
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
function tabi_custom_nav_menu_item($group_id, $name, $url, $order, $parent = 0) {
    $item = new stdClass();
    $item->group_id = $group_id;    
    $item->ID = 1000000 + $order + $parent;
    $item->db_id = $item->ID;
    $item->post_title = $name;    
    $item->url = $url;
    $item->menu_order = $order;
    $item->menu_item_parent = $parent;
    $item->type = 'custom';
    $item->object = 'custom';
    $item->object_id = '';
    $item->post_parent = $parent;
    $item->post_type = 'nav_menu_item';
    //$item->classes = array("bp-menu", "bp-my-groups-sub-nav");
    $item->classes = array("bp-menu");
    $item->target = '';
    $item->attr_title = '';
    $item->description = '';
    $item->xfn = '';
    $item->status = '';
    $item = wp_setup_nav_menu_item($item);
    
    return $item;
}

function replace_buddypanel_groups_icons ($title, $item, $args, $depth) {
    if ( isset($item->group_id)){
        $avatar = bp_core_fetch_avatar(
            array(
                'item_id'    => $item->group_id,
                'avatar_dir' => 'group-avatars',
                'object'     => 'group',
                'type'       => 'thumb',
                'alt'        => sprintf( __( 'Group logo of %s', 'buddyboss' ), $item->post_title ),
                'css_id'     => false,
                'class'      => 'avatar',
                'width'      => 27,
                'height'     => 27,
            )
        );      
        
        // If No avatar found, provide some backwards compatibility.        
        if ( strpos($avatar, bb_get_buddyboss_group_avatar('thumb') )) {
            $avatar = '<i class="_mi _before buddyboss bb-icon-groups" aria-hidden="true"></i>';
            
            // Other examples of how you can set the fallback icon
            
            // font awesome
            //$avatar = '<i class="_mi _before fa fa-diamond" aria-hidden="true"></i>';            
            
            // URL to an image
            //$avatar = '<img src="https://cdn3.iconfinder.com/data/icons/google-material-design-icons/48/ic_location_on_48px-512.png" class="avatar" width="25" height="25" alt="esc_attr( $item->name )">';
            
            // You'll likely need to adjust the margin size. And perhaps the width and heights above.
            $avatar .= "<span style='margin-left: 10px;'>{$item->post_title}</span>";    
        }
        else {
            $avatar .= "<span style='margin-left: 15px;'>{$item->post_title}</span>";
        }        
        return $avatar;
    }
    return $title;
}

add_filter ('nav_menu_item_title', 'replace_buddypanel_groups_icons', 10, 4);
