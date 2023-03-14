add_filter( 'bp_get_following', 'follow_user_roles' , 10, 1 );
function follow_user_roles( $ids ) {
	static $follow_ids = null;
	
	if ( is_null( $follow_ids ) ) {
		//comment/uncomment the user roles - or add your own - to select which types of users will be auto-followed
		$args = array(
			'role__in'    => [
				'administrator',
				//'editor',
				//'author',
				//'contributor',
				//'subscriber'
			],
		);
		$follow_ids = array_merge(array_map('intval',$ids), array_column(get_users( $args ), 'ID'));
	}
	
	return $follow_ids;
}
