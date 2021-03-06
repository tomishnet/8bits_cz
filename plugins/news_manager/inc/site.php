<?php if (!defined('IN_GS')) {die('you cannot load this page directly.');}

/**
 * News Manager front-end functions.
 */


/*******************************************************
 * @function nm_show_page
 * param $index - page index (pagination)
 * @action show posts on news page
 */
function nm_show_page($index=0) {
  global $NMPOSTSPERPAGE, $NMSHOWEXCERPT;
  $index = intval($index);
  $posts = nm_get_posts();
  $pages = array_chunk($posts, intval($NMPOSTSPERPAGE), true);
  if ($index >= 0 && $index < sizeof($pages))
    $posts = $pages[$index];
  else
    $posts = array();
  if (!empty($posts)) {
    $showexcerpt = ($NMSHOWEXCERPT == 'Y');
    foreach ($posts as $post)
      nm_show_post($post->slug, $showexcerpt);
    if (sizeof($pages) > 1)
      nm_show_navigation($index, sizeof($pages));
  } else {
    echo '<p>' . i18n_r('news_manager/NO_POSTS') . '</p>';
  }
}


/*******************************************************
 * @function nm_show_archive
 * param $id - unique archive id
 * @action show posts by archive
 */
function nm_show_archive($archive) {
  $archives = nm_get_archives();
  if (array_key_exists($archive, $archives)) {
    $posts = $archives[$archive];
    foreach ($posts as $slug)
      nm_show_post($slug, true);
   }
}


/*******************************************************
 * @function nm_show_tag
 * param $id - unique tag id
 * @action show posts by tag
 */
function nm_show_tag($tag) {
  $tags = nm_get_tags();
  if (array_key_exists($tag, $tags)) {
    $posts = $tags[$tag];
    foreach ($posts as $slug)
      nm_show_post($slug, true);
  }
}


/*******************************************************
 * @function get_search_results()
 * @action search posts by keyword(s)
 */
function nm_show_search_results() {
  $keywords = @explode(' ', $_POST['keywords']);
  $posts = nm_get_posts();
  foreach ($keywords as $keyword) {
    $match = array();
    foreach ($posts as $post) {
      $data = getXML(NMPOSTPATH . "$post->slug.xml");
      $content = $data->title . $data->content;
      if (stripos($content, $keyword) !== false)
        $match[] = $post;
    }
    $posts = $match;
  }
  if (!empty($posts)) {
    echo '<p>' . i18n_r('news_manager/FOUND') . '</p>';
    foreach ($posts as $post)
      nm_show_post($post->slug, true);
  } else {
    echo '<p>' . i18n_r('news_manager/NOT_FOUND') . '</p>';
  }
}


/*******************************************************
 * @function nm_show_post
 * param $slug post slug
 * param $excerpt - if TRUE, print only a short summary
 * @action show the requested post on front-end news page
 */
function nm_show_post($slug, $excerpt=false) {
  $file = NMPOSTPATH . "$slug.xml";
  if (dirname(realpath($file)) == realpath(NMPOSTPATH)) // no path traversal
    $post = @getXML($file);
  if (!empty($post) && $post->private != 'Y') {
    $url     = nm_get_url('post') . $slug;
    $title   = stripslashes($post->title);
    $date    = nm_get_date(i18n_r('news_manager/DATE_FORMAT'), strtotime($post->date));
    $content = strip_decode($post->content);
    if ($excerpt) $content = nm_create_excerpt($content);
    # print post data ?>
    <div class="nm_post">
      <h3 class="nm_post_title">
        <a href="<?php echo $url; ?>"><?php echo $title; ?></a>
      </h3>
      <p class="nm_post_date"><?php echo i18n_r('news_manager/PUBLISHED') . " $date"; ?></p>
      <div class="nm_post_content"><?php echo $content; ?></div>
      <?php
      # print tags, if any
      if (!empty($post->tags)) {
        echo '<p class="nm_post_meta"><b>' . i18n_r('news_manager/TAGS') . ':</b>';
        $tags = explode(',', $post->tags);
        foreach ($tags as $tag) {
          $url = nm_get_url('tag') . $tag;
          echo " <a href=\"$url\">$tag</a>";
        }
        echo '</p>';
      }
      
      # single post page?
      if (strstr($_SERVER['QUERY_STRING'], "post=$slug")) {
        # store post title
        global $NMPOSTTITLE;
        $NMPOSTTITLE = $title;
        # show "go back" link
        echo '<p class="nm_post_back"><a href="javascript:history.back()">';
        i18n('news_manager/GO_BACK');
        echo '</a></p>';
      }
      ?>
    </div>
    <?php
  } else {
    echo '<p>' . i18n_r('news_manager/NOT_EXIST') . '</p>';
  }
}


/*******************************************************
 * @function nm_show_navigation
 * param $index - current page index
 * param $total - total number of subpages
 * @action provides links to navigate between subpages
 */
function nm_show_navigation($index, $total) {
  $url = nm_get_url('page');
  echo '<div class="nm_page_nav">';
  if ($index < $total - 1) {
    ?>
    <div class="left">
      <a href="<?php echo $url . ($index+1); ?>">
        <?php i18n('news_manager/OLDER_POSTS'); ?>
      </a>
    </div>
    <?php
  }
  if ($index > 0) {
    ?>
    <div class="right">
      <a href="<?php echo ($index > 1) ? $url . ($index-1) : substr($url, 0, -6); ?>">
        <?php i18n('news_manager/NEWER_POSTS'); ?>
      </a>
    </div>
    <?php
  }
  echo '</div>';
}


/***********************************************************************************
 * SINCE Version 2.3
***********************************************************************************/

/*******************************************************
 * @function nm_post_title
 * param $before Text to place before the title. Defaults to ''
 * param $after Text to place after the title. Defaults to ''
 * param $echo Display (true) or return (false)
 * @action Display or return the post title. Returns false if not on single post page
 */
function nm_post_title($before='', $after='', $echo=true) {
  global $NMPAGEURL;
  $title = false;
  if (isset($_GET['post']) && strval(get_page_slug(false)) == $NMPAGEURL) {
    global $NMPOSTTITLE;
    if ($NMPOSTTITLE) {
      # use previously read post title
      $title = $before.$NMPOSTTITLE.$after;
      if ($echo) echo $title;
    }
  }
  return $title;
}

?>