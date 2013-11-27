<?php 

function damien_dbc_admin_add_help_tab () {
    global $dbc_backup_admin;
    $screen = get_current_screen();
    //echo $screen->id;
    /*
     * Check if current screen is My Admin Page
     * Don't add help tab if it's not
     */
    if ( $screen->id != $dbc_backup_admin )
        return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
    'id'      => 'vpl-start',
    'title'   => __('Getting Started', 'vpl'),
    'content' => '<ol>
					<li>Add a <a target="_blank" href="post-new.php?post_type=page">new Page</a></li>
					<li>Add the shortcode [dbc_isotope]</li>
					<li>Remember to give your page a name</li>
					<li>Publish the page</li>
					<li>View your page with Isotope</li>
					</ol>',
));
 
/*
$screen->add_help_tab( array(
    'id'      => 'vpl-modules',
    'title'   => __('vpl Modules', 'vpl'),
    'content' => "HTML for help content",
));
*/
 
$screen->add_help_tab( array(
    'id'      => 'paging',
    'title'   => __('Paging', 'vpl'),
    'content' => '<p>Paging is cool - but taxing</p><ol>
					<li>Use the shortcode attribute [dbc_isotope paging=on]</li>
					<li>Default number of posts is now 20</li>
					<li>Careful not to enable paging and set your posts per page too high - as it eats memory</li>
					</ol>',
));    
    $screen->add_help_tab( array(
        'id'	=> 'shortcode',
        'title'	=> __('Shortcode Options'),
        'content'	=> '<p>' . __( '<p>You can configure the number of posts to show. Here are a couple of examples</p>
				<ul>
				<li> [dbc_isotope posts=5] will show 5 posts</li>
				<li> [dbc_isotope posts=-1] will show all posts</li>
				<li> [dbc_isotope posts=-1 post_type=feedback] will show all posts from custom post type feedback</li>
				<li> [dbc_isotope cats=5] will show 10 posts from category 5</li> 
				<li> [dbc_isotope order=DESC] defaults to most recent posts first but you can change this to ASC to go with oldest.</li>

						</ul>' ) . '</p>',
    ) );
    
    
    
    $screen->set_help_sidebar( '<p>' .'Related Content'. '</p> <p>Thanks for Going Pro with version' . ISOTOPEVERSION .
					'<ul><li><a target="_blank" href="http://wordpress.damien.co/isotope/?utm_source=WordPress&utm_medium=isotope-pro-installed&utm_content=help&utm_campaign=Isotope-Layouts">Help & FAQs</a></li>
					<li><a target="_blank" href="http://wordpress.damien.co/?utm_source=WordPress&utm_medium=isotope-pro-installed&utm_content=help&utm_campaign=Isotope-Layouts">More WordPress Tips & Ideas</a></li>
					</ul>'
    
    );
}
