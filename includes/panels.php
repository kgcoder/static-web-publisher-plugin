<?php

function get_panels($post) {


    $link = home_url( "/sw/v1/comments/{$post->post_name}");

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
<side-panel><?php echo $link ?></side-panel>
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
</panels>



    <?php

    $output = ob_get_clean();

    return $output;

}