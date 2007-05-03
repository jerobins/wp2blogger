<?php
// http://dev.d10e.net/files/scripts/wp-atom.phps
// Atom 1.0 feed generator for WordPress
// Distributed under the terms of the GNU General Public License v2
// Based on the Atom 0.3 generator for WP 1.5 <http://trac.wordpress.org/file/tags/1.5.1.3/wp-atom.php>
// the patch in trac by comatoast <http://trac.wordpress.org/ticket/1526>
// and modifications by Federico <http://511.dabomb.com.ar/testcase/atom1.0/atom-source.txt>
// Further modifications by Ben de Groot <http://berkano.net/>, version 19/02/2006
// and hacked to output a full feed for dumping to xml file, James E. Robinson, III

if (empty($wp)) {
   require_once('wp-config.php');
   $posts_per_page = 4000;
   wp();
}

// this function ideally should go into wp-includes/feed-functions.php
function atom_enclosure() {
    global $id, $post;
    if (!empty($post->post_password) && ($_COOKIE['wp-postpass_'.COOKIEHASH] != $post->post_password)) return;

    $custom_fields = get_post_custom();
    if( is_array( $custom_fields ) ) {
        while( list( $key, $val ) = each( $custom_fields ) ) {
            if( $key == 'enclosure' ) {
                if (is_array($val)) {
                    foreach($val as $enc) {
                        $enclosure = split( "\n", $enc );
                        print "<link rel=\"enclosure\" href=\"".trim( htmlspecialchars($enclosure[ 0 ]) )."\" length=\"".trim( $enclosure[ 1 ] )."\" type=\"".trim( $enclosure[ 2 ] )."\"/>\n";
                    }
                }
            }
        }
    }
}

header('Content-type: text/xml; charset=' . get_settings('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_settings('blog_charset').'"?'.'>'."\n"; ?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xml:lang="<?php echo get_option('rss_language'); ?>"
      <?php do_action('atom_ns'); ?>>
    <title><?php bloginfo_rss('name'); ?></title>
    <subtitle><?php bloginfo_rss('description'); ?></subtitle>
    <id><?php bloginfo('url'); ?>/</id>
    <link rel="self" type="application/atom+xml" href="http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"/>
    <link rel="alternate" type="<?php bloginfo('html_type'); ?>" href="<?php bloginfo_rss('home'); ?>/"/>
    <updated><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></updated>
    <rights>Copyright <?php echo mysql2date('Y', get_lastpostdate('blog'), 0); ?></rights>
    <generator uri="http://wordpress.org/" version="<?php bloginfo_rss('version'); ?>">WordPress</generator>
    <?php do_action('atom_head'); ?>
<?php $items_count = 0; 
   if ($posts) { 
         foreach ($posts as $post) { 
            start_wp(); ?>
    <entry>
        <title type="html"><![CDATA[ <?php the_title_rss(); ?> ]]></title>
        <link rel="alternate" type="<?php bloginfo('html_type'); ?>" href="<?php permalink_single_rss(); ?>"/>
        <id><?php permalink_single_rss(); ?></id>
        <published><?php echo get_post_time('Y-m-d\TH:i:s\Z', true); ?></published>
        <updated><?php echo mysql2date('Y-m-d\TH:i:s\Z',$post->post_modified_gmt); ?></updated>
<?php
    $categories = get_the_category();
    foreach ($categories as $cat) { ?>
        <category scheme="http://www.blogger.com/atom/ns#"
                  term="<?php echo strtolower($cat->cat_name); ?>" />
<?php } ?>
<?php if ( get_settings('rss_use_excerpt') ) : ?>
        <summary type="html"><![CDATA[ <?php the_excerpt_rss(); ?> ]]></summary>
        <?php atom_enclosure(); ?>
<?php else: ?>
        <content type="html"><![CDATA[ <?php the_content('', 0, '') ?> ]]></content>
<?php endif;
        do_action('atom_entry'); ?>
    </entry>
<?php $items_count++; 
      if (($items_count == get_settings('posts_per_rss')) 
          && empty($m)) { 
         #break; 
      } 
   } 
} ?>
</feed>
