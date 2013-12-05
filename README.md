=== DW Social Feed ===
Contributors: DesignWall
Donate link: none
Tags: feed, social, auto post, post, cron, facebook, twitter, youtube, flickr, vimeo, instagram
Requires at least: 3.4.1
Tested up to: 3.4.1
Stable tag: 1.0.5
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin import content automatically from popular Social media sites 

== Description ==
    
    DW Social Feed plugin is a plugin which allows user to get content automatically from popular Social media sites( Facebook, Twitter, Youtube, Vimeo, Instagram, Flickr ) or RSS Feed. After that, these contents will be stored as posts in specific categories. You can choose category, author, posttype for these contents. Setup time-based job scheduler in import feed content for all or each social feed. Also Support multi profiles/usernames for each social feed

    Facebook:  Providing a Facebook page name where module will get content from, and the mapped category where Facebook post will be stored into.
    E.g: https://www.facebook.com/feeds/page.php?id=123144964369587&format=json

    Twitter:  Providing a Twitter query string (E.g: by specific account <strong>from:joomlart</strong>, by hashtag <strong>#joomlart</strong>, by mention <strong>@joomlart</strong>). See https://dev.twitter.com/docs/api/1/get/search.

    Youtube:  Providing a Youtube author name
    E.g http://gdata.youtube.com/feeds/api/videos?max-results=20&alt=json&format=5&author=joomlart

    Vimeo:  Providing a Vimeo Username and account type   
    E.g http://vimeo.com/api/v2/[Account type]/[Username]/videos.json

    Instagram:  Providing Instagram what was use for get images from 
    E.g: http://widget.stagram.com/rss/n/[username]

    Flickr:  Providing a Flickr ID: <br />(E.g: <strong>58736703@N00</strong> ).
    E.g http://api.flickr.com/services/feeds/photos_public.gne?format=php_serial&id=58736703@N00

    RSS: Providing a rss link
    E.g http://feeds.feedburner.com/joomlart/blog

== Installation ==

1. Upload `dw social feed` package to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add new profile for social media sites what are use for get content
4. Setup time-based job scheduler for all or each social feed then save option. (You can choose `run now` for start cron job immediately )


== Frequently Asked Questions ==

= What is cron? =
cron is the time-based job scheduler in Unix-like computer operating systems. cron enables users to schedule jobs (commands or shell scripts) to run periodically at certain times or dates

== Screenshots ==

1. General settings
2. Profile setting

== Changelog ==

= 1.0 =
Initial version of this plugin.

= 1.0.1 =
Remove bugs.

= 1.0.5 =
* Add title for posts from facebook
* Update twitter api 1.1 for get tweets function

== Upgrade Notice == 

= 1.0 =
Initial version of this plugin.

= 1.0.1 =
Remove bugs.
