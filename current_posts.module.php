<?php

/**
 * @file
 * A block module that displays the blogs posts from the last two days.
 */

/*
Tutorial: 		https://www.drupal.org/node/1095546
Name: 			hook_help 	
Location: 		modules/system/system.api.php 	
Description: 	Provide online user help. 
list:			drupal 7.x hooks https://api.drupal.org/api/drupal/includes!module.inc/group/hooks/7

current_posts_help provides help and additional information about the module to the user. 
*/
/**
 * Implements hook_help().
 *
 * Displays help and module information.
 *
 * @param path 
 *   Which path of the site we're using to display help
 * @param arg 
 *   Array that holds the current path as returned from arg() function
 */

function current_posts_help($path, $arg) {
	/*
	The $path parameter provides context for the help: where in Drupal or the module the user is when they are accessing help. The recommended way to process this variable is with a switch statement. This code pattern is common in Drupal modules.
	(in this case it is located at the following link (relative path) admin/help#current_posts
	is used by Drupal core to link from the main help page (/admin/help or ?q=admin/help).
	*/
	switch($path) {
		/*
		A switch statement is used here because it is typical for the help function to offer more than one page containing help text.
		*/
		case "admin/help#current_posts":
			return '<p>' . t("Displays links to nodes created on this date") . '<p>';
			/*
			t() function that wraps the text marks it for translation. This function not only allows for translation, it can give your code an added layer of security
			*/
			break;
	}

}

/**
 * The doc block simply identifies the hook.
 * go to the API and call up the hook for further info
 * https://api.drupal.org/api/drupal/includes!module.inc/group/hooks/7
 * Implements hook_block_info().
 */

function current_posts_block_info() {
  $blocks['current_posts'] = array(
    // The name that will appear in the block list.
    'info' => t('Current posts'),
    // Default setting.
    'cache' => DRUPAL_CACHE_PER_ROLE,
  );
  return $blocks;
  /*
  * The return value takes the form of an associative array
  * The only required value is 'info'
  */
}

/**
 * Custom content function. 
 * Retrieve Posts from the last two days
 * Set beginning and end dates, retrieve posts from database
 * saved in that time period.
 * 
 * @return 
 *   A result set of the targeted posts.
 */

function current_posts_contents(){
  //Get today's date.
  $today = getdate();
  //Calculate the date two days ago.
  //mktime() is a PHP function http://php.net/manual/en/function.mktime.php
  //Returns the Unix timestamp corresponding to the arguments given
  
  $start_time = mktime(0, 0, 0,$today['mon'],($today['mday'] - 2), $today['year']);
  //Get all posts from two days ago to the present.
  //int time(void) - PHP var that returns the current time measured in the number of seconds
  //since the Unix Epoch (January 1 1970 00:00:00 GMT)
  // http://php.net/manual/en/function.time.php
  $end_time = time();

  //Use Database API to retrieve current posts.
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title', 'created'))
    ->condition('status', 1) //Published.
    ->condition('created', array($start_time, $end_time), 'BETWEEN')
    ->orderBy('created', 'DESC') //Most recent first.
    ->execute(); 
  return $query;  
}

/**
 * Implements hook_block_view().
 * 
 * Prepares the contents of the block.
 */
function current_posts_block_view($delta = '') {
  switch ($delta) {
    case 'current_posts':
    /*
    This function returns two values, 'subject', which is the title of the block, and 'content', which is self-documenting
    */
      $block['subject'] = t('Current posts');
      /*
      implement the most basic of checks, 'access content'
      list of permission names, go to People > List (tab)
      */
      if (user_access('access content')) {
        // Use our custom function to retrieve data.
        $result = current_posts_contents();
        // Array to contain items for the block to render.
        $items = array();
        // Iterate over the resultset and format as links.
        /*
        use our custom function to save the data into the $result variable. The $items variable gives us a place to store the processed data. We then iterate over the result set, processing each item and formatting it as a link
        */

        foreach ($result as $node) {
          $items[] = array(
          	/*
          	actual link is created by the l() function
          	The first parameter sets the link text, in this case the node's title. The second parameter is the actual link path. The path to any node is always "node/#", where # is the ID number of the node. l() uses this path to generate appropriate links, adjusting the URL to the installation's URL configuration
          	*/
            'data' => l($node->title, 'node/' . $node->nid),
          ); 
        }

        /*
        Drupal's presentation layer is a pluggable system known as the theme layer. 
         Each theme can take control of most of Drupal's output, and has complete control over the CSS. 
         First, we allow for the possibility of no content that fits our criteria. Your module's block should appear whether or not new content from the day before yesterday exists on your site.
        */


       // No content in the day before yesterday.
        if (empty($items)) {
          $block['content'] = t('No posts available.');  
        } 
        else {
          // Pass data through theme function.
          $block['content'] = theme('item_list', array(
            'items' => $items));
        }
      }
    return $block;
  }
  
}



/* I have tried to implement a module (block moudule
which would retrieve posts containing the "president" keyword,
where presidents would be an array of 3 presidents
but I needed more time to finish it....:)

function current_posts_contents(){


  Get Presidents Array
  $presidents = array('Trump', 'Clinton', 'EU-president'); 

  //Use Database API to retrieve current posts.
  $query = db_select('node', 'n')
    ->fields('n', array('nid', 'title', 'created'))
    ->condition('status', 1) //Published.
    ->condition('title', 
    	foreach ($presidents as $president) {
    		if(strpos($query, $president !== FALSE)) {
    			$query->orderBy('created', 'DESC') //Most recent first.
    			$query->execute();
    		}
    		return $query;  
    		
    	} else {
    		echo "No posts with PRESIDENT Found";//return FALSE
    	});*/
    
    

  



