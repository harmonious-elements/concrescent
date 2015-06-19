<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/badges.php';

$conn = get_db_connection();

if (isset($_POST['ba'])) {
	$badge_artwork_id = $_POST['ba'];
} else if (isset($_GET['ba'])) {
	$badge_artwork_id = $_GET['ba'];
} else {
	header('Location: badge_checkin.php');
	exit(0);
}

$badge_artwork = get_badge_artwork($badge_artwork_id, $conn);
if (!$badge_artwork) {
	header('Location: badge_checkin.php');
	exit(0);
}

if (isset($_POST['img']) || isset($_GET['img'])) {
	if (!echo_badge_artwork($badge_artwork['filename'])) {
		header('Content-Type: image/png');
		$image = imagecreate(300, 200);
		$bg = imagecolorallocate($image, 255, 255, 255);
		imagefilledrectangle($image, 0, 0, 300, 200, $bg);
		imagepng($image);
		imagedestroy($image);
	}
	exit(0);
}

$only_print = (
	isset($_COOKIE['badge_printing_only_print']) ?
	explode(',', $_COOKIE['badge_printing_only_print']) :
	false
);
if ($only_print && !in_array($badge_artwork_id, $only_print)) {
	echo '<html><head>';
	echo '<title>Error</title>';
	echo '<style>*{margin:0;padding:0;}h3,p{margin:20px;}</style>';
	echo '</head><body>';
	echo '<h3>This computer cannot be used to print this type of badge.</h3>';
	echo '<p>Please instruct the badge holder to move to another line.</p>';
	echo '</body></html>';
	exit(0);
}

$badge_artwork_fields = get_badge_artwork_fields($badge_artwork_id, $conn);

if (isset($_POST['data'])) {
	$badge_holder = json_decode($_POST['data'], true);
} else if (isset($_GET['data'])) {
	$badge_holder = json_decode($_GET['data'], true);
} else {
	if (isset($_POST['t'])) {
		$badge_holder_table = $_POST['t'];
	} else if (isset($_GET['t'])) {
		$badge_holder_table = $_GET['t'];
	} else {
		header('Location: badge_checkin.php');
		exit(0);
	}
	if (isset($_POST['id'])) {
		$badge_holder_id = $_POST['id'];
	} else if (isset($_GET['id'])) {
		$badge_holder_id = $_GET['id'];
	} else {
		header('Location: badge_checkin.php');
		exit(0);
	}
	$badge_holder = get_badge_holder($badge_holder_table, $badge_holder_id, $conn);
	if (!$badge_holder) {
		header('Location: badge_checkin.php');
		exit(0);
	}
	increment_print_count($badge_holder_table, $badge_holder_id, $conn);
}

if (isset($_POST['w'])) {
	$width = htmlspecialchars($_POST['w']);
} else if (isset($_GET['w'])) {
	$width = htmlspecialchars($_GET['w']);
} else if (isset($_COOKIE['badge_printing_width'])) {
	$width = htmlspecialchars($_COOKIE['badge_printing_width']);
} else {
	$width = $badge_printing_width;
}
if (isset($_POST['h'])) {
	$height = htmlspecialchars($_POST['h']);
} else if (isset($_GET['h'])) {
	$height = htmlspecialchars($_GET['h']);
} else if (isset($_COOKIE['badge_printing_height'])) {
	$height = htmlspecialchars($_COOKIE['badge_printing_height']);
} else {
	$height = $badge_printing_height;
}
if (isset($_POST['v'])) {
	$vertical = !!(int)$_POST['v'];
} else if (isset($_GET['v'])) {
	$vertical = !!(int)$_GET['v'];
} else if (isset($_COOKIE['badge_printing_vertical'])) {
	$vertical = !!(int)$_COOKIE['badge_printing_vertical'];
} else {
	$vertical = $badge_printing_vertical;
}
if (isset($_POST['blank'])) {
	$blank = !!(int)$_POST['blank'];
} else if (isset($_GET['blank'])) {
	$blank = !!(int)$_GET['blank'];
} else if (isset($_COOKIE['badge_printing_blank'])) {
	$blank = !!(int)$_COOKIE['badge_printing_blank'];
} else {
	$blank = false;
}

header('Content-Type: text/html; charset=utf-8');
echo '<html>';
echo '<head>';
echo '<title>CONcrescent Badge '.htmlspecialchars($badge_holder['id_string']).'</title>';
if ($badge_printing_external_stylesheet) {
	echo '<link rel="stylesheet" href="';
	echo htmlspecialchars($badge_printing_external_stylesheet);
	echo '">';
}
echo '<style>';
echo '*{margin:0;padding:0;}';
echo '.badge{position:relative;';
if (!$badge_artwork['vertical'] == !$vertical) {
	echo 'width:'.$width.';height:'.$height.';';
} else {
	echo 'width:'.$height.';height:'.$width.';';
	echo '-webkit-transform-origin:0 0 0;-webkit-transform:translateY('.$height.') rotate(-90deg);';
	echo '-moz-transform-origin:0 0 0;-moz-transform:translateY('.$height.') rotate(-90deg);';
	echo '-ms-transform-origin:0 0 0;-ms-transform:translateY('.$height.') rotate(-90deg);';
	echo 'transform-origin:0 0 0;transform:translateY('.$height.') rotate(-90deg);';
}
if (!$blank) {
	echo 'background:url(badge_print.php?img&ba='.(int)$badge_artwork_id.');';
	echo 'background-repeat:no-repeat;background-position:center;background-size:100% 100%;';
}
echo '}';
echo '.field{position:absolute;text-align:center;';
echo 'display:-webkit-flex;-webkit-align-content:center;-webkit-align-items:center;-webkit-justify-content:center;';
echo 'display:flex;align-content:center;align-items:center;justify-content:center;}';
foreach ($badge_artwork_fields as $i => $f) {
	echo '.f'.(int)$i.'{';
	echo 'top:'.htmlspecialchars($f['top']).'%;';
	echo 'left:'.htmlspecialchars($f['left']).'%;';
	echo 'right:'.htmlspecialchars($f['right']).'%;';
	echo 'bottom:'.htmlspecialchars($f['bottom']).'%;';
	echo 'font-family:'.htmlspecialchars($f['font_family']).';';
	echo 'font-weight:'.($f['font_weight_bold']?'bold':'normal').';';
	echo 'font-style:'.($f['font_style_italic']?'italic':'normal').';';
	if ($badge_holder['age'] >= 18) {
		echo 'background:'.htmlspecialchars($f['background']).';';
		echo 'color:'.htmlspecialchars($f['color']).';';
	} else {
		echo 'background:'.htmlspecialchars($f['background_minors']).';';
		echo 'color:'.htmlspecialchars($f['color_minors']).';';
	}
	echo '}';
}
echo '</style>';
echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js')) . '"></script>';
echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmui.js')) . '"></script>';
echo '<script type="text/javascript">';
echo '$(window).load(function(){';
	echo 'setTimeout(function(){';
		echo '$(\'.field\').each(function(){cmui.fitText($(this));});';
		echo 'setTimeout(function(){';
			echo 'window.print();';
			echo 'setInterval(function(){';
				echo 'window.close();';
			echo '},100);';
		echo '},100);';
	echo '},100);';
echo '});';
echo '</script>';
echo '</head>';
echo '<body>';
echo '<div class="badge">';
foreach ($badge_artwork_fields as $i => $f) {
	$content = $badge_holder[$f['field_type']];
	if ($content) {
		echo '<div class="field f'.(int)$i.'">';
		echo htmlspecialchars($content);
		echo '</div>';
	}
}
echo '</div>';
echo '</body>';
echo '</html>';