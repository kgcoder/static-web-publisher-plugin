<?php

function get_panels($post) {

    $permalink = get_permalink($post->ID);

    $path_part = preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $permalink);

    $link = home_url( "/comments{$path_part}");

    ob_start();

    ?>

<panels>
<panel-style textColor="white" bgColor="black" borderWidth="1px" borderColor="black"></panel-style>
<top-panel>
<!-- <panel-style textColor="red" bgColor="green" borderWidth="1px" borderColor="black"></panel-style> -->
<logo src="https://www.shutterstock.com/shutterstock/photos/2370919043/display_1500/stock-vector-red-box-circle-cursive-text-cocaine-blow-snow-crack-spoof-parody-funny-wave-label-logo-icon-sign-2370919043.jpg" href="https://google.com"></logo>
<a href="https://google.com">Google</a>
<a href="https://youtube.com">YouTube</a>

</top-panel>

<?php if (has_comment_section($post)): ?>
<side-panel><?php echo $link ?></side-panel>
          
        <?php endif; ?>



<bottom-panel>
<!-- <panel-style textColor="yellow" bgColor="black" borderWidth="5px" borderColor="red"></panel-style> -->
<section>
<title>About</title>
<a href="https://google.com">Google</a>
<a href="https://youtube.com">YouTube</a>
<a href="https:/x.com">X.com</a>
</section>

<section>
<title>About2</title>
<a href="https://google.com">Google2</a>
<a href="https://youtube.com">YouTube2</a>
<a href="https:/x.com">X.com2</a>
</section>

<section>
<title>About3</title>
<a href="https://google.com">Google3</a>
<a href="https://youtube.com">YouTube3</a>
<a href="https:/x.com">X.com3</a>
</section>


</bottom-panel>
</panels><?php

    $output = ob_get_clean();

    return $output;

}


function has_comment_section($post) {
    // Check if comments are globally allowed and post-specific status
    $global_comments_setting = get_option('default_comment_status');
    $theme_supports_comments = function_exists('comments_template');

    if ($post && $post->comment_status === 'open' && $global_comments_setting === 'open' && $theme_supports_comments) {
        return true;
    }

    return false;
}