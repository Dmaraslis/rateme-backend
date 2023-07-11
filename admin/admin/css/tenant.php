<?php
header("Content-type: text/css; charset: UTF-8");

//barber

$mainColor = '#2d2d2d';
$secondaryColor = '#1d1d1d';
$extraColor = '#0c8656';
$extraColorLight = '#0dda92';
$extraColorDark = '#0a9465';
$fontColor = 'whitesmoke';
$hoveringColor = '#0a63454a';



//palete 1
/*$mainColor = '#483D8B';
$secondaryColor = '#4B0082';
$extraColor = '#6A5ACD';
$extraColorLight = '#836FFF';
$extraColorDark = '#7B68EE';
$fontColor = 'whitesmoke';
$hoveringColor = '#483D8B';
$iconsColor = '';*/


//palete 2
//$mainColor = '#708090';
//$secondaryColor = '#778899';
//$extraColor = '#4682B4';
//$extraColorLight = '#5CACEE';
//$extraColorDark = '#36648B';
//$fontColor = 'whitesmoke';
//$hoveringColor = '#483D8B';
//$iconsColor = '';


//palete 3
//$mainColor = '#FFB6C1';
//$secondaryColor = '#FFC1C1';
//$extraColor = '#FF69B4';
//$extraColorLight = '#FF85C1';
//$extraColorDark = '#FF1493';
//$fontColor = 'whitesmoke';
//$hoveringColor = '#483D8B';
//$iconsColor = '';


//palete 4 -- nice
/*$mainColor = '#1F3B60';
$secondaryColor = '#2A4D80';
$extraColor = '#FF2400';
$extraColorLight = '#FF4500';
$extraColorDark = '#B22222';
$fontColor = 'whitesmoke';
$hoveringColor = '#2b5084';
$iconsColor = '';*/
?>


:root {
  --main-color: <?php echo $mainColor; ?>;
  --secondary-color: <?php echo $secondaryColor; ?>;
  --extra-color: <?php echo $extraColor; ?>;
  --extra-colorlight: <?php echo $extraColorLight; ?>;
  --extra-colordark: <?php echo $extraColorDark; ?>;
  --font-color: <?php echo $fontColor; ?>;
  --hovering-color: <?php echo $hoveringColor; ?>;
  --icons-color: <?php echo $iconsColor; ?>;
}
