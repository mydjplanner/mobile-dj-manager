<?php

/**
 * Contains all equipment and package related functions
 *
 * @package		MDJM
 * @subpackage	Venues
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * Retrieve the package name, description, cost
 *
 * @param	str		$slug		Slug name of the package
 *		
 *
 */
function get_package_details( $slug )	{
	if( empty( $slug ) )
		return false;
	
	$packages = mdjm_get_packages();
	
	if( empty( $packages[$slug] ) )
		return false;
	
	$package['slug'] = $slug;
	$package['name'] = stripslashes( esc_attr( $packages[$slug]['name'] ) );
	$package['desc'] = stripslashes( esc_textarea( $packages[$slug]['desc'] ) );
	$package['equipment'] = $packages[$slug]['equipment'];
	$package['cost'] = $packages[$slug]['cost'];
	
	return $package;
	
} // get_package_details

/**
 * Retrieve all addons by dj
 *
 * @param	int|arr	$user_id	Required: User ID of DJ, or array of DJ User ID's
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_dj( $user_id )	{
	// We work with an array
	if( !is_array( $user_id ) )
		$users = array( $user_id );
		
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
		
	asort( $equipment );
	
	// Loop through the addons and filter for the given user(s)
	foreach( $equipment as $addon )	{
		$users_have = explode( ',', $addon[8] );
		
		foreach( $users as $user )	{			
			if( !in_array( $user, $users_have ) )
				continue 2; // Continue from the foreach( $equipment as $addon ) loop
		}
			
		$addons[] = $addon;
	}
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_dj

/**
 * Retrieve all addons within the given category
 *
 * @param	str		$cat		Required: Slug of the category for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_cat( $cat )	{
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
		
	asort( $equipment );
	
	// Loop through the addons and filter for the given category
	foreach( $equipment as $addon )	{
		if( $addon[5] != $cat )
			continue;
		
		$addons[] = $addon;	
	}
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_cat

/**
 * Retrieve all addons within the given package slug
 *
 * @param	str		$slug		Required: Slug of the package for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_package_slug( $slug )	{
	$package = mdjm_get_package_by_slug( strtolower( $slug ) );
	
	// No package or the package has no addons, return false
	if( empty( $package ) || empty( $package['equipment'] ) )
		return false;
	
	$package_items = explode( ',', $package['equipment'] );
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
	
	foreach( $equipment as $addon )	{
		if( !in_array( $addon[1], $package_items ) )
			continue;
			
		$addons[] = $addon;	
	}
	
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_package_slug

/**
 * Retrieve all addons within the given package
 *
 * @param	str		$name		Required: Name of the package for which to search
 *
 * @return	arr		$addons		Array of all addons
 */
function mdjm_addons_by_package_name( $name )	{
	$package = mdjm_get_package_by_name( $name );
	
	// No package or the package has no addons, return false
	if( empty( $package ) || empty( $package['equipment'] ) )
		return false;
	
	$package_items = explode( ',', $package['equipment'] );
	$equipment = mdjm_get_addons();
	
	// No addons, return false
	if( empty( $equipment ) )
		return false;
	
	foreach( $equipment as $addon )	{
		if( !in_array( $addon[1], $package_items ) )
			continue;
			
		$addons[] = $addon;	
	}
	
	// Return the results, or false if none
	return !empty( $addons ) ? $addons : false;
} // mdjm_addons_by_package_name

/*
 * Retrieve the addon name
 *
 * @param	str		$slug	The slug name of the addon
 * @return	str		$addon	The display name of the addon
 */
function get_addon_name( $slug )	{
	if( empty( $slug ) )
		return false;
			
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment[$slug] ) || empty( $equipment[$slug][0] ) )
		return false;
		
	$addon = stripslashes( esc_attr( $equipment[$slug][0] ) );
	
	return $addon;
	
} // get_addon_name

/*
 * Retrieve the addon category, name, decription & cost
 *
 *
 *
 */
function get_addon_details( $slug )	{
	if( empty( $slug ) )
		return false;
		
	$cats = get_option( 'mdjm_cats' );
	
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment[$slug] ) )
		return false;
		
	$addon['slug'] = $slug;
	$addon['cat'] = stripslashes( esc_attr( $cats[$equipment[$slug][5]] ) );
	$addon['name'] = stripslashes( esc_attr( $equipment[$slug][0] ) );
	$addon['desc'] = stripslashes( esc_textarea( $equipment[$slug][4] ) );
	$addon['cost'] = $equipment[$slug][7];
	
	return $addon;
	
} // get_addon_details

/*
 * Output HTML code for Addons checkbox list
 *
 * @param	arr		$settings		Settings for the dropdown
 *									'name'				Optional: The name of the input. Defaults to 'event_addons'
 *									'class'				Optional: Class of the input field
 *									'checked'			Optional: ARRAY of initially checked options
 *									'dj'				Optional: The ID of the DJ to present package for (default current user)
 *									'package'			Optional: Package slug for which to exclude addons if they exist in that package
 *									'title'				Optional: Add addon description to the title element of each option
 *									'cost'				Optional: Display the price of the package (default true)
 * @ return	HTML output for select field
 */
function mdjm_addons_checkboxes( $settings='' )	{
	global $current_user;
	
	// Set the values based on the array passed
	$check_name = isset( $settings['name'] ) ? $settings['name'] : 'event_addons';
	$check_id = isset( $settings['id'] ) ? $settings['id'] : $check_name;
	$check_dj = ( !empty( $settings['dj'] ) ? $settings['dj'] : ( is_user_logged_in() ? $current_user->ID : '' ) );
	$check_cost = isset( $settings['cost'] ) ? $settings['cost'] : false;
	
	$mdjm_check = '';
	
	$equipment = mdjm_get_addons();
	
	if( empty( $equipment ) )
		$mdjm_check .= __( 'No Addons Available', 'mobile-dj-manager' ) . "\r\n";
		
	else	{
		asort( $equipment );
	// All addons
		$cats = get_option( 'mdjm_cats' );
		if( !empty( $cats ) )
			asort( $cats );
		
		foreach( $cats as $cat_key => $cat_value )	{				
			$header = false;
			
			// Create an array of options grouped by category
			foreach( $equipment as $item )	{
				// If the addon is not enabled, do not show it
				if( empty( $item[6] ) || $item[6] != 'Y' )
					continue;
					
				// If the addon is part of an assigned package, exlude it
				if( !empty( $settings['package'] ) )	{
					$packages = mdjm_get_packages();
					$package_items = explode( ',', $packages[$settings['package']]['equipment'] );
					
					if( !empty( $package_items ) && in_array( $item[1], $package_items ) )
						continue;	
				}
				
				// If the specified DJ does not have the addon, do not show it	
				if( !empty( $select_dj ) )	{
					$djs_have = explode( ',', $item[8] );
					
					if( !in_array( $select_dj, $djs_have ) )
						continue;
				}
				
				if( $item[5] == $cat_key )	{
					if( empty( $header ) )	{
						$mdjm_check .= '<span class="font-weight: bold;">' . stripslashes( $cat_value ) . '</span><br />' . "\r\n";
						$header = true;
					}
						
						$mdjm_check .= '<input type="checkbox" name="' . $check_name . '[]" ';
						$mdjm_check .= 'id="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '"';
						$mdjm_check .= ( !empty( $settings['class'] ) ? 
								' class="' . $settings['class'] . '"' : '' );
								
						$mdjm_check .= ' value="' . stripslashes( esc_attr( $item[1] ) ) . '"';
						
						if( !empty( $settings['checked'] ) && in_array( $item[1], $settings['checked'] ) )
							$mdjm_check .= ' checked="checked"';
						
						$mdjm_check .= ' />&nbsp;' . "\r\n";
						
						$mdjm_check .= ( !empty( $settings['title'] ) && !empty( $item[4] ) ? 
							'<span title="' . stripslashes( $item[4] ) . '">' : '' );
						
						$mdjm_check .= '<label for="' . $check_name . '_' . stripslashes( esc_attr( $item[1] ) ) . '">' . stripslashes( $item[0] );
						
						$mdjm_check .= $check_cost == true ? ' - ' . mdjm_currency_filter( mdjm_format_amount( $item[7] ) ) : '';
						
						$mdjm_check .= '</label>' . ( !empty( $settings['title'] ) && !empty( $item[4] ) ? '</span>' : '' ) . '<br />' .  "\r\n";
				}
				
			}
		}
	}
	
	return $mdjm_check;
	
} // mdjm_addons_checkboxes