<?php

function send_post($post){
    if ($post) {

        $permalink = get_permalink($post->ID);
        
        $title = $post->post_title;
        $htmlContent = $post->post_content;

        // header('Content-Type: text/plain');
        // echo  $htmlContent;

        $pattern = '/<!-- wp:embed \{"url":"https:\/\/www\.youtube\.com\/watch\?v=([^"]+)",.*"className":"wp-embed-aspect-(\d+)-(\d+) wp-has-aspect-ratio"\} -->.*<div class="wp-block-embed__wrapper">\s*(https:\/\/www\.youtube\.com\/watch\?v=[^<]+)\s*<\/div>.*<!-- \/wp:embed -->/sU';
        
        $callback = function ($matches) {
            $youtubeId = $matches[1];
            $width = 560;// $matches[2];
            $height = 315;// $matches[3];
            return "<iframe width=\"$width\" height=\"$height\" src=\"https://www.youtube.com/embed/$youtubeId\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe><h3>hello</h3>";
        };

        $htmlContent = preg_replace_callback($pattern, $callback, $htmlContent);

        $htmlContent = strip_wp_tags($htmlContent);
        
        //$allowed_tags = ['p', 'a', 'strong', 'h1', 'h2', 'h3', 'h4', 'img', 'figure'];
        
       // $htmlContent = strip_unwanted_tags($htmlContent,$allowed_tags);
        
        // $htmlContent = preg_replace('/<img[^>]*>/', "<br>$0<br>", $htmlContent);






        //$testVideo = "<p>sfsdfdsflkj</p><iframe width=\"560px\" height=\"315px\" src=\"https://www.youtube.com/embed/oVfHeWTKjag\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe><h3 class=\"p1\"><span class=\"s1\">What I did about it</span></h3>";

//wp_strip_all_tags

//http://swplugintest.local/my-test-post/
        $finalContent = '<h1>' . $title . "</h1>" . /*$testVideo .*/ $htmlContent . "<p>---</p><p><a href=\"" . $permalink . "\">" . "Original page</a></p>";

       // $link = home_url( "/sw/v1/comments/{$post->post_name}");
        $panels = get_panels($post);

        // Output the simplified content
        header('Content-Type: text/plain');
        echo '<hdoc>' . $panels .'<body>' . $finalContent . '</body></hdoc>';
    } else {
        // Handle post not found
        status_header(404);
        echo 'Post not found';
    }
}