<?php
// /USER CONFIGURATION PLEASE MODIFY
$uploadPassword = ""; //set a password so its not a public file drop.(RECOMMENDED)
$fileExpirationDays = 7; //how long to save the uploaded files on the server.
$useOwln = true;
// /SCRIPT; PLEASE DON'T MODIFY BELOW UNLESS YOU KNOW WHAT YOUR ARE DOING.
session_start();
// make nesisary folder structure
if (!file_exists('./uploads')) {
	mkdir('./uploads', 0777, true);
}
// /auto clean uploads greater than the expiration
$files = glob("uploads/*");
$now = time();
foreach($files as $file) {
	if (is_file($file)) {
		if ($now - filemtime($file) >= 60 * 60 * 24 * $fileExpirationDays) {
			unlink($file);
		}
	}
}
// public function helpers
function get_content($URL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
function getOwlnLink($path)
{
	$link = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']) , "/") . "/uploads/" . urlencode($path);
	return json_decode(get_content('http://direct.owln.xyz/owlnadddirect/' . $link . '/owlnpath/') , true) ["link"];
}
// handle bans
if (isset($_SESSION['banned'])) {
	if ($now - $_SESSION['banned'] < 60 * 10) {
		header('Content-Type: application/json');
		die('{"error":"You\'ve been banned please try again later"}');
	}
}
// authenticator
$authed = false;
if (empty($_SESSION['authed'])) {
	if (isset($_POST['password'])) {
		if ($uploadPassword === $_POST['password']) {
			// properly authenticated
			$_SESSION['authed'] = true;
			$authed = true;
		}
		else {
			// bad password attempt block for 10 minutes;
			$_SESSION['banned'] = $now + 60 * 10;
			header('Content-Type: application/json');
			die('{"error":"You\'ve been banned please try again later"}');
		}
	}
	else {
		// prompt for password
	}
}
else {
	if (isset($_GET['logout'])) {
		unset($_SESSION['authed']);
		header("Location:  http://" . $_SERVER['HTTP_HOST'] . rtrim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']) , "/"));
		die();
	}
	$authed = true;
	$upload_success = null;
	$upload_error = '';
	if (!empty($_FILES['files'])) {
		$info = pathinfo($_FILES['files']['name'][0]);
		$ext = $info['extension']; // get the extension of the file
		$newname = "FFM-" . preg_replace("/[^A-Za-z0-9 ]/", '', str_replace("." . $ext, "", $_FILES['files']['name'][0])) . "." . $ext;
		if (move_uploaded_file($_FILES['files']['tmp_name'][0], "uploads/" . $newname)) {
			$link = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']) , "/") . "/uploads/" . urlencode($newname);
			if ($useOwln) {
				$link = getOwlnLink($newname);
			}
			$upload_success = true;
			$upload_error = $link;
		}
		else {
			$upload_success = false;
			$upload_error = "File could not be uploaded.";
		}
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(array(
			'success' => $upload_success,
			'error' => $upload_error
		));
		die();
	}
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
  <head>
    <meta charset="utf-8">
    <title>FileFlyMe
    </title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,300italic,400" />
    <style>
      body,
      .ad,
      .sm
      {
        font-family: Lucida Grande, Helvetica Neue, Helvetica, Arial, Verdana, sans-serif;
      }
      a
      {
        color: currentColor;
        text-decoration: none;
      }
      .clearfix::after
      {
        content: '';
        display: table;
        clear: both;
      }
      /*
      AD
      */
      .ad
      {
        width: 9.375rem;
        /* 150 */
        color: #444;
        color: rgba( 0, 0, 0, .75 );
        background-color: #fff;
        background-color: rgba( 255, 255, 255, .5 );
        position: fixed;
        z-index: 1000;
        top: 0.625rem;
        /* 10 */
        left: 0.625rem;
        /* 10 */
        padding: 0.5rem 0.625rem;
        /* 8 10 */
      }
      .ad--dark
      {
        color: #ddd;
        color: rgba( 255, 255, 255, .75 );
        background-color: #111;
        background-color: rgba( 0, 0, 0, .5 );
      }
      .ad__close
      {
        width: 0.625rem;
        /* 10 */
        height: 0.625rem;
        /* 10 */
        background-color: #777;
        background-color: rgba( 0, 0, 0, .5 );
        border-radius: 50%;
        position: absolute;
        z-index: 1;
        top: -0.25rem;
        /* 4 */
        right: -0.25rem;
        /* 4 */
        -webkit-transition: -webkit-transform .25s ease-in-out;
        transition: transform .25s ease-in-out;
      }
      .ad--dark .ad__close
      {
        background-color: #ddd;
        background-color: rgba( 255, 255, 255, .75 );
      }
      .ad__close:hover,
      .ad__close:focus
      {
        -webkit-transform: scale( 1.25 );
        -ms-transform: scale( 1.25 );
        transform: scale( 1.25 );
      }
      #carbonads
      {
        font-size: 0.875rem;
        /* 14 */
        letter-spacing: -0.071em;
        /* 1 (14) */
        line-height: 1.125rem;
        /* 18 */
      }
      #carbonads a
      {
        color: currentColor;
        display: block;
        margin-top: 0.313rem;
        /* 5 */
      }
      #carbonads .carbon-poweredby
      {
        font-size: 0.75rem;
        /* 12 */
        text-transform: uppercase;
        color: #aaa;
        color: rgba( 0, 0, 0, .25 );
      }
      .ad--dark #carbonads .carbon-poweredby
      {
        color: #999;
        color: rgba( 255, 255, 255, .25 );
      }
      /*
      SOCIAL MEDIA
      */
      .sm
      {
        width: 100%;
        height: 2.5rem;
        /* 40 */
        color: #444;
        color: rgba( 0, 0, 0, .75 );
        background-color: #fff;
        background-color: rgba( 255, 255, 255, .5 );
        overflow: hidden;
        position: fixed;
        z-index: 1001;
        bottom: 0;
        left: 0;
        padding: 0.625rem 1.25rem 0;
        /* 10 20 */
      }
      .sm--dark
      {
        color: #ddd;
        color: rgba( 255, 255, 255, .75 );
        background-color: #111;
        background-color: rgba( 0, 0, 0, .5 );
      }
      .sm ul
      {
      }
      .sm li
      {
        float: right;
        margin-left: 1rem;
      }
      .sm li:first-child
      {
        float: left;
        margin-left: 0;
      }
      .sm .googleplus-one
      {
        max-width: 60px;
      }
      .sm .twitter-follow > *:not( :first-child ),
      .sm .twitter-share > *:not( :first-child )
      {
        display: none;
      }
      @media screen{
        @media ( min-width: 0px ) /* everything but Internet Explorer and Safari â‰¤6 */
        {
          .sm li:last-child
          {
            opacity: 0;
            -webkit-transition: opacity .25s ease-in-out;
            transition: opacity .25s ease-in-out;
          }
          .sm:hover li:last-child
          {
            opacity: 1;
          }
        }
      }
      .sm__back
      {
        font-size: 0.875rem;
        /* 14 */
        font-weight: bold;
        color: currentColor;
        text-transform: uppercase;
        position: relative;
      }
      .sm__back::before
      {
        width: 0;
        height: 0;
        border: 0.313rem solid transparent;
        /* 5 */
        border-left: none;
        border-right-color: currentColor;
        content: '';
        display: inline-block;
        position: relative;
        left: 0;
        margin-right: 0.313rem;
        /* 5 */
        -webkit-transition: left .25s ease-in-out;
        transition: left .25s ease-in-out;
      }
      .sm__back:hover::before,
      .sm__back:focus::before
      {
        left: -0.313rem;
        /* 5 */
      }
      /*
      RESPONSIVENESS
      */
      @media screen and (max-width: 40em), screen and (max-height: 40em) /* 640 */
      {
        .ad,
        .sm
        {
          display: none;
        }
      }
      html
      {
      }
      body
      {
        font-family: Roboto, sans-serif;
        color: #3D3D3D;
        background-color: #212428;
        padding: 5rem 1.25rem;
        /* 80 20 */
      }
      .container
      {
        width: 100%;
        max-width: 680px;
        /* 800 */
        text-align: center;
        margin: 0 auto;
      }
      .container h1
      {
        font-size: 42px;
        font-weight: 300;
        color: #3D3D3D;
        margin-bottom: 40px;
      }
      .container h1 a:hover,
      .container h1 a:focus
      {
        color: #595959;
      }
      .container nav
      {
        margin-bottom: 40px;
      }
      .container nav a
      {
        border-bottom: 2px solid #3D3D3D;
        display: inline-block;
        padding: 4px 8px;
        margin: 0 5px;
      }
      .container nav a.is-selected
      {
        font-weight: 700;
        color: #212428;
        border-bottom-color: currentColor;
      }
      .container nav a:not( .is-selected ):hover,
      .container nav a:not( .is-selected ):focus
      {
        border-bottom-color: #3D3D3D;
      }
      .container footer
      {
        color: #3D3D3D;
        margin-top: 40px;
      }
      .container footer p + p
      {
        margin-top: 1em;
      }
      .container footer a:hover,
      .container footer a:focus
      {
        color: #595959;
      }
      .box
      {
        font-size: 1.25rem;
        /* 20 */
        background-color: #3D3D3D;
        color: #212428;
        position: relative;
        padding: 100px 20px;
      }
      .box.has-advanced-upload
      {
        outline: 2px dashed #3D3D3D;
        outline-offset: -10px;
        -webkit-transition: outline-offset .15s ease-in-out, background-color .15s linear;
        transition: outline-offset .15s ease-in-out, background-color .15s linear;
      }
      .box.is-dragover
      {
        outline-offset: -20px;
        outline-color: #3D3D3D;
        background-color: #fff;
      }
      .box__dragndrop,
      .box__icon
      {
        display: none;
      }
      .box.has-advanced-upload .box__dragndrop
      {
        display: inline;
      }
      .box.has-advanced-upload .box__icon
      {
        width: 100%;
        height: 80px;
        fill: #212428;
        display: block;
        margin-bottom: 40px;
      }
      .box.is-uploading .box__input,
      .box.is-success .box__input,
      .box.is-error .box__input
      {
        visibility: hidden;
      }
      .box__uploading,
      .box__success,
      .box__error
      {
        display: none;
      }
      .box.is-uploading .box__uploading,
      .box.is-success .box__success,
      .box.is-error .box__error
      {
        display: block;
        position: absolute;
        top: 50%;
        right: 0;
        left: 0;
        -webkit-transform: translateY( -50% );
        transform: translateY( -50% );
      }
      .box__uploading
      {
        font-style: italic;
      }
      .box__success
      {
        -webkit-animation: appear-from-inside .25s ease-in-out;
        animation: appear-from-inside .25s ease-in-out;
      }
      @-webkit-keyframes appear-from-inside
      {
        from	{
          -webkit-transform: translateY( -50% ) scale( 0 );
        }
        75%		{
          -webkit-transform: translateY( -50% ) scale( 1.1 );
        }
        to		{
          -webkit-transform: translateY( -50% ) scale( 1 );
        }
      }
      @keyframes appear-from-inside
      {
        from	{
          transform: translateY( -50% ) scale( 0 );
        }
        75%		{
          transform: translateY( -50% ) scale( 1.1 );
        }
        to		{
          transform: translateY( -50% ) scale( 1 );
        }
      }
      .box__restart
      {
        font-weight: 700;
      }
      .box__restart:focus,
      .box__restart:hover
      {
        color: #212428;
      }
      .js .box__file
      {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
      }
      .js .box__file + label
      {
        max-width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        display: inline-block;
        overflow: hidden;
      }
      .js .box__file + label:hover strong,
      .box__file:focus + label strong,
      .box__file.has-focus + label strong
      {
        color: #212428;
      }
      .js .box__file:focus + label,
      .js .box__file.has-focus + label
      {
        outline: 1px dotted #000;
        outline: -webkit-focus-ring-color auto 5px;
      }
      .js .box__file + label *
      {
        /* pointer-events: none; */ /* in case of FastClick lib use */
      }
      .no-js .box__file + label
      {
        display: none;
      }
      .no-js .box__button
      {
        display: block;
      }
      .box__button
      {
        font-weight: 700;
        color: #212428;
        background-color: #212428;
        display: none;
        padding: 8px 16px;
        margin: 40px auto 0;
      }
      .box__button:hover,
      .box__button:focus
      {
        background-color: #3D3D3D;
      }
      .btn {
        -webkit-border-radius: 3;
        -moz-border-radius: 3;
        border-radius: 3px;
        color: #3D3D3D;
        font-size: 18px;
        background: #212428;
        padding: 5px 5px 5px 5px;
        border: solid #000000 0px;
        text-decoration: none;
      }
      .btn:hover {
        background: #595959;
        text-decoration: none;
      }
    </style>
    <!-- remove this if you use Modernizr -->
    <script>(function(e,t,n){
        var r=e.querySelectorAll("html")[0];
        r.className=r.className.replace(/(^|\s)no-js(\s|$)/,"$1js$2")}
            )(document,window,0);
    </script>
  </head>
  <body>
    <div class="container" role="main">
      <svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="900.000000pt" height="399.000000pt" viewBox="0 0 900.000000 399.000000" preserveAspectRatio="xMidYMid meet" style="width:200px;height: auto;">
        <metadata>
        </metadata>
        <g transform="translate(0.000000,399.000000) scale(0.100000,-0.100000)" fill="#3d3d3d" stroke="none">
          <path d="M8743 3978 c3 -18 -132 -25 -462 -27 -196 -1 -277 -5 -315 -15 -48 -14 -92 -25 -241 -63 -33 -8 -82 -21 -110 -28 -27 -8 -77 -21 -110 -29 -33 -8 -89 -22 -125 -31 -36 -9 -90 -23 -120 -30 -30 -7 -84 -21 -120 -31 -119 -31 -164 -43 -230 -59 -36 -9 -79 -21 -96 -26 -17 -5 -33 -9 -37 -9 -12 0 -8 55 6 75 7 11 19 37 26 59 11 33 11 40 -2 53 -16 16 -3 18 -238 -33 -37 -8 -76 -14 -88 -14 -11 0 -35 -13 -53 -28 -18 -16 -69 -56 -113 -89 -105 -79 -132 -108 -140 -146 -3 -18 -12 -48 -19 -67 -8 -19 -22 -62 -31 -95 -10 -33 -24 -75 -31 -93 -8 -18 -14 -38 -14 -45 -1 -41 -391 -456 -725 -771 l-90 -85 16 -33 c21 -43 47 -132 53 -183 3 -22 10 -69 15 -105 10 -66 5 -141 -11 -174 -6 -11 -6 -22 -1 -27 6 -6 33 23 68 73 32 46 82 115 110 153 28 39 63 88 78 111 24 39 133 124 158 124 7 0 9 -8 4 -22 -13 -41 -18 -553 -6 -577 18 -32 43 -12 70 56 42 107 50 128 55 153 8 34 23 56 32 47 11 -10 22 -107 23 -193 1 -54 6 -77 19 -94 10 -13 24 -20 30 -16 6 3 32 37 59 74 47 67 48 67 51 160 l4 94 24 -23 c13 -12 24 -28 24 -35 0 -7 22 -60 50 -118 53 -113 73 -129 104 -85 15 22 15 22 21 -7 9 -40 34 -119 58 -182 36 -97 177 -199 170 -122 -2 14 -6 46 -9 73 -4 32 -2 47 5 47 7 0 14 -8 17 -17 20 -61 50 -126 64 -138 15 -14 19 -14 29 -1 10 12 10 50 2 173 -11 165 -24 250 -47 318 -18 51 -18 88 -1 83 6 -3 13 -13 15 -24 2 -10 10 -34 18 -51 23 -53 40 -95 58 -140 9 -24 23 -62 32 -85 46 -120 76 -181 90 -185 32 -10 41 25 47 189 6 156 3 199 -21 244 -5 10 -18 39 -27 64 -9 25 -23 58 -31 73 -16 33 -18 57 -5 57 8 0 100 -129 110 -155 2 -5 17 -26 32 -46 16 -20 29 -39 29 -42 0 -3 18 -32 40 -65 26 -38 61 -73 98 -98 31 -22 89 -63 128 -91 77 -58 85 -61 104 -38 11 13 -1 57 -66 253 l-79 237 -112 169 c-109 163 -122 186 -105 186 4 0 127 -103 273 -228 147 -126 280 -233 295 -239 16 -6 58 -18 94 -27 36 -10 88 -23 115 -31 276 -74 254 -70 272 -52 21 21 26 16 -282 347 -38 41 -74 80 -80 86 -5 6 -32 35 -59 64 -45 48 -407 327 -483 373 -18 10 -33 23 -33 28 0 16 36 9 106 -20 38 -16 104 -43 147 -60 42 -17 111 -44 152 -61 41 -16 110 -44 153 -61 297 -119 296 -119 346 -119 24 0 68 -5 97 -11 30 -6 92 -14 139 -19 92 -9 205 -23 342 -44 60 -8 91 -9 98 -2 26 26 7 46 -126 128 -74 46 -147 91 -162 100 -15 10 -36 22 -47 28 -11 6 -32 18 -47 28 -111 70 -297 180 -313 185 -29 8 -107 36 -130 46 -11 5 -29 12 -40 14 -11 3 -45 15 -75 27 -30 11 -86 32 -125 45 -38 13 -95 33 -125 45 -30 12 -86 32 -125 45 -110 38 -154 54 -173 64 -9 5 -28 14 -42 19 l-25 11 29 0 c16 1 68 -3 115 -8 47 -6 158 -17 246 -26 199 -20 386 -39 575 -60 193 -21 293 -22 360 -2 30 9 84 23 120 32 90 23 167 44 221 61 26 8 55 14 66 14 39 0 60 52 26 63 -10 3 -43 15 -73 27 -30 12 -68 25 -85 30 -16 5 -55 18 -85 30 -30 11 -86 32 -125 45 -38 13 -101 35 -139 49 l-69 26 -473 0 c-288 0 -475 4 -479 9 -3 6 9 12 27 15 18 2 69 10 113 17 44 7 103 16 130 20 97 14 191 30 260 45 39 8 84 14 100 14 17 0 46 5 65 10 19 6 67 15 105 20 39 5 96 13 128 19 31 6 83 16 115 22 31 6 89 14 129 19 94 12 145 27 174 54 185 167 310 276 318 276 18 0 12 29 -9 50 -18 18 -33 20 -130 20 -83 0 -109 -3 -107 -12z"/>
          <path d="M7 3905 c-25 -27 4 -60 218 -248 112 -100 165 -127 244 -127 17 0 63 -6 103 -14 40 -8 118 -22 173 -31 55 -9 134 -23 175 -31 42 -7 87 -14 100 -14 14 0 57 -6 95 -14 39 -8 113 -22 165 -30 52 -9 136 -22 185 -30 50 -9 111 -18 138 -21 26 -4 47 -10 47 -14 0 -17 -105 -21 -529 -21 -418 0 -439 -1 -491 -21 -30 -11 -68 -24 -85 -29 -16 -5 -55 -18 -85 -30 -30 -12 -68 -25 -85 -30 -16 -5 -55 -18 -85 -30 -30 -12 -68 -25 -85 -29 -58 -15 -96 -41 -93 -63 3 -21 31 -32 143 -57 22 -5 76 -19 120 -31 44 -12 96 -26 115 -30 19 -4 62 -15 95 -24 62 -16 140 -14 380 13 99 11 303 32 550 56 88 9 196 20 240 26 44 5 91 9 105 9 21 0 19 -3 -15 -21 -22 -11 -71 -31 -110 -44 -38 -13 -95 -33 -125 -45 -30 -12 -86 -32 -125 -45 -38 -13 -95 -34 -125 -46 -30 -11 -66 -23 -80 -26 -14 -3 -29 -9 -35 -13 -5 -4 -26 -13 -45 -18 -19 -6 -60 -20 -90 -32 -30 -12 -67 -25 -83 -30 -24 -8 -49 -22 -160 -88 -12 -7 -34 -21 -49 -30 -14 -9 -92 -57 -173 -107 -80 -49 -158 -98 -173 -107 -15 -10 -34 -21 -42 -26 -8 -4 -37 -21 -63 -37 -39 -24 -47 -35 -45 -55 2 -20 8 -25 28 -24 29 1 178 20 270 35 36 6 108 14 160 19 52 5 113 14 135 20 22 5 66 10 98 10 33 0 71 6 85 14 15 8 47 21 72 31 25 9 59 23 75 30 30 12 83 33 160 62 22 9 54 21 70 28 17 7 50 21 75 30 25 10 59 23 75 30 17 8 66 28 110 45 44 18 95 38 113 46 37 16 62 18 62 6 0 -5 -42 -39 -92 -75 -51 -37 -111 -81 -133 -97 -22 -17 -85 -63 -140 -104 -55 -40 -120 -95 -145 -122 -25 -27 -99 -107 -165 -179 -65 -71 -141 -153 -167 -181 -116 -122 -119 -127 -86 -148 11 -7 28 -6 55 1 48 14 192 52 333 88 36 9 79 23 95 30 25 11 176 136 325 269 20 17 65 56 100 86 36 30 78 67 94 82 31 29 46 35 46 17 0 -5 -34 -60 -75 -120 -41 -61 -75 -113 -75 -116 0 -3 -13 -22 -28 -42 -37 -48 -67 -116 -92 -206 -5 -16 -14 -46 -21 -65 -6 -19 -17 -51 -24 -70 -7 -19 -18 -51 -24 -70 -37 -104 -41 -139 -20 -153 18 -11 32 -2 199 119 71 52 130 110 130 128 0 2 34 54 75 115 41 62 75 116 75 121 0 6 5 10 10 10 6 0 15 11 20 25 10 27 30 34 30 11 0 -7 -13 -44 -29 -82 -76 -179 -71 -156 -64 -335 6 -170 12 -199 41 -199 18 0 36 31 65 105 8 22 29 76 47 120 18 44 39 98 47 120 8 22 21 54 28 70 8 17 15 39 17 49 2 11 9 21 16 24 16 5 16 -35 -2 -84 -19 -58 -44 -233 -51 -365 -5 -97 -3 -118 10 -129 12 -10 18 -8 35 11 10 13 19 31 20 39 0 9 6 24 14 35 7 11 16 32 20 48 11 48 29 32 22 -20 -13 -101 -13 -98 14 -101 39 -4 132 97 155 168 9 30 24 70 31 88 8 18 14 43 14 56 0 32 10 39 31 20 37 -33 53 -15 118 129 17 37 31 70 31 73 0 4 10 23 23 43 l22 36 6 -100 c5 -97 6 -102 47 -161 50 -73 64 -86 85 -78 19 7 24 33 32 175 7 112 17 146 35 119 6 -8 13 -26 16 -40 3 -14 12 -41 19 -60 65 -168 73 -182 100 -160 13 10 15 50 14 244 0 127 -4 258 -9 291 -4 33 -4 64 1 69 4 5 34 -11 68 -36 58 -43 117 -116 228 -281 25 -37 50 -66 56 -64 7 2 10 40 8 108 -1 71 3 120 13 154 9 28 16 63 16 78 0 15 6 40 14 55 7 15 17 45 20 67 7 40 7 40 -71 110 -156 139 -477 472 -608 629 -22 27 -48 58 -57 69 -10 11 -18 26 -18 33 0 7 -6 28 -14 46 -7 18 -21 60 -31 93 -9 33 -23 76 -30 95 -8 19 -16 49 -20 65 -4 17 -26 49 -53 74 -83 77 -240 191 -264 191 -21 0 -92 14 -232 45 -32 7 -65 11 -72 8 -20 -7 -26 -39 -13 -65 26 -50 32 -63 39 -90 5 -15 5 -30 0 -33 -10 -6 -83 10 -145 31 -23 8 -49 14 -59 14 -10 0 -39 7 -65 15 -25 8 -75 21 -111 30 -36 9 -92 23 -125 32 -33 8 -87 21 -120 29 -33 8 -82 21 -110 29 -27 7 -77 20 -110 28 -33 9 -88 23 -122 31 -35 9 -89 22 -120 30 -32 8 -78 20 -103 26 -27 8 -179 16 -375 21 -181 4 -397 11 -480 15 -82 3 -153 3 -158 -1z"/>
          <path d="M3798 2476 c-24 -30 -54 -77 -67 -103 -12 -26 -27 -56 -32 -65 -36 -70 -85 -315 -75 -375 24 -143 31 -174 48 -228 30 -93 68 -164 129 -246 117 -155 263 -253 470 -313 76 -22 343 -23 396 -1 19 8 44 15 55 15 11 0 32 6 46 14 15 7 54 26 86 42 65 32 184 119 229 168 84 91 163 227 196 337 17 58 24 98 37 225 5 50 -19 213 -41 282 -38 116 -143 291 -175 292 -8 0 -46 -20 -85 -45 -97 -63 -172 -97 -295 -131 -53 -16 -99 -19 -255 -18 -178 0 -195 2 -277 28 -99 31 -216 86 -268 126 -73 57 -75 57 -122 -4z m1082 -259 c94 -25 154 -79 184 -167 19 -55 20 -85 4 -146 -15 -53 -64 -115 -122 -152 -36 -22 -52 -26 -126 -26 -76 -1 -90 2 -132 28 -60 35 -99 85 -123 158 -18 55 -18 58 0 117 28 94 89 155 185 187 46 16 75 16 130 1z m-543 -234 c3 -9 -23 -13 -99 -15 l-103 -3 -5 -107 c-3 -58 -8 -110 -13 -114 -17 -17 -25 16 -29 109 -2 73 -7 100 -18 107 -8 5 -59 10 -112 10 -90 0 -110 5 -91 24 4 3 110 5 237 4 169 -2 230 -6 233 -15z m219 -331 c53 -41 72 -95 60 -176 -6 -35 -24 -68 -77 -141 -39 -52 -74 -95 -77 -95 -11 0 -26 13 -37 32 -5 9 -31 48 -58 86 -65 97 -81 145 -66 199 14 52 67 106 111 114 46 9 119 -1 144 -19z"/>
          <path d="M4764 2181 c-84 -14 -151 -88 -160 -178 -11 -103 23 -171 109 -221 29 -16 55 -22 103 -22 81 0 128 23 180 85 36 45 38 50 38 124 1 80 -10 110 -58 156 -50 48 -131 69 -212 56z"/>
          <path d="M3891 1182 c-117 -121 -135 -144 -249 -315 -136 -204 -145 -220 -129 -236 15 -15 42 -14 57 4 7 8 16 15 20 15 4 0 45 25 90 55 45 30 89 55 96 55 13 0 -9 -47 -89 -190 -62 -111 -87 -161 -87 -174 0 -18 27 -28 46 -18 14 8 276 254 303 284 8 9 18 15 23 12 13 -8 9 -77 -7 -150 -20 -85 -20 -238 0 -332 8 -40 15 -88 15 -106 0 -36 21 -62 43 -53 7 3 21 36 31 74 9 37 23 94 31 125 8 32 15 67 15 79 0 11 7 35 14 52 8 18 20 48 25 67 9 28 10 18 5 -50 -3 -47 -1 -106 5 -130 5 -25 18 -91 28 -147 17 -99 19 -103 43 -103 19 0 29 9 43 39 9 20 17 44 17 52 0 8 6 23 14 34 7 11 16 31 19 45 13 56 40 120 58 136 19 17 19 15 20 -67 1 -108 11 -181 25 -186 7 -2 19 3 28 12 13 13 16 39 16 128 0 62 4 118 9 126 11 16 32 -24 66 -124 65 -191 68 -197 103 -193 24 3 28 8 33 48 3 25 11 74 18 110 7 36 16 84 20 108 5 23 5 61 0 85 -10 51 -1 124 12 92 4 -11 8 -32 8 -46 1 -15 8 -40 16 -55 8 -16 15 -38 15 -49 0 -22 24 -130 48 -212 11 -39 20 -53 33 -53 24 0 39 30 39 77 0 21 7 73 15 116 19 98 19 186 1 274 -19 89 -23 165 -11 170 10 3 46 -29 219 -194 103 -98 117 -107 141 -89 20 14 23 8 -150 322 -14 26 -24 48 -21 51 6 6 63 -24 103 -53 17 -13 34 -24 37 -24 3 0 25 -14 51 -31 73 -50 112 -21 61 46 -14 18 -26 36 -26 38 0 2 -43 69 -96 148 -83 125 -181 247 -248 311 -20 19 -42 24 -48 11 -6 -14 -99 -53 -203 -86 -107 -33 -362 -30 -491 7 -62 17 -166 65 -218 101 l-39 26 -66 -69z"/>
        </g>
      </svg>
      <h1>
        <a href="">FileFlyMe
        </a>
      </h1>
      <form method="post" action="" enctype="multipart/form-data" novalidate class="box">
        <div class="box__input">
          <?php
if ($authed) { ?>
          <svg class="box__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43">
            <path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/>
          </svg>
          <input type="file" name="files[]" id="file" class="box__file" />
          <label for="file">
            <strong>Choose a file
            </strong>
            <span class="box__dragndrop"> or drag it here
            </span>.
          </label>
          <br />
          <br />
          <br />
          <a href="?logout">Log Out?
          </a>
          <button type="submit" class="box__button">Upload
          </button>
          <?php
}
else { ?>
          <input type="password" class="btn" name="password" id="password" placeholder="Password" class="box_pass" />
          <button type="submit" class="btn">Login
          </button>
          <?php
} ?>
        </div>
        <div class="box__uploading">Uploading&hellip;
        </div>
        <div class="box__success">Done! 
          <p>File is accessible from: 
            <br />
            <a href="" target=â€_blankâ€ id="successlink">
            </a>
          </p>
          <br /> 
          <a href="/?" class="box__restart" role="button">Upload more?
          </a>
        </div>
        <div class="box__error">Error! 
          <span>
          </span>. 
          <a href="/?" class="box__restart" role="button">Try again!
          </a>
        </div>
      </form>
      <footer>
        <p>You can upload your files using this tool and get a short link.
        </p>
        <p>This is an open source project located on 
          <a href="https://github.com/techyowl/fileflyme">github
          </a>.
        </p>
      </footer>
    </div>
    <!--
JQUERY DEPENDENCY
-->
    <!--
<script src="jquery-v1.min.js"></script>
<script>
'use strict';
;( function( $, window, document, undefined )
{
// feature detection for drag&drop upload
var isAdvancedUpload = function()
{
var div = document.createElement( 'div' );
return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
}();
// applying the effect for every form
$( '.box' ).each( function()
{
var $form		 = $( this ),
$input		 = $form.find( 'input[type="file"]' ),
$label		 = $form.find( 'label' ),
$errorMsg	 = $form.find( '.box__error span' ),
$restart	 = $form.find( '.box__restart' ),
droppedFiles = false,
showFiles	 = function( files )
{
$label.text( files.length > 1 ? ( $input.attr( 'data-multiple-caption' ) || '' ).replace( '{count}', files.length ) : files[ 0 ].name );
};
// letting the server side to know we are going to make an Ajax request
$form.append( '<input type="hidden" name="ajax" value="1" />' );
// automatically submit the form on file select
$input.on( 'change', function( e )
{
showFiles( e.target.files );
$form.trigger( 'submit' );
});
// drag&drop files if the feature is available
if( isAdvancedUpload )
{
$form
.addClass( 'has-advanced-upload' ) // letting the CSS part to know drag&drop is supported by the browser
.on( 'drag dragstart dragend dragover dragenter dragleave drop', function( e )
{
// preventing the unwanted behaviours
e.preventDefault();
e.stopPropagation();
})
.on( 'dragover dragenter', function() //
{
$form.addClass( 'is-dragover' );
})
.on( 'dragleave dragend drop', function()
{
$form.removeClass( 'is-dragover' );
})
.on( 'drop', function( e )
{
droppedFiles = e.originalEvent.dataTransfer.files; // the files that were dropped
showFiles( droppedFiles );
$form.trigger( 'submit' ); // automatically submit the form on file drop
});
}
// if the form was submitted
$form.on( 'submit', function( e )
{
// preventing the duplicate submissions if the current one is in progress
if( $form.hasClass( 'is-uploading' ) ) return false;
$form.addClass( 'is-uploading' ).removeClass( 'is-error' );
if( isAdvancedUpload ) // ajax file upload for modern browsers
{
e.preventDefault();
// gathering the form data
var ajaxData = new FormData( $form.get( 0 ) );
if( droppedFiles )
{
$.each( droppedFiles, function( i, file )
{
ajaxData.append( $input.attr( 'name' ), file );
});
}
// ajax request
$.ajax(
{
url: 			$form.attr( 'action' ),
type:			$form.attr( 'method' ),
data: 			ajaxData,
dataType:		'json',
cache:			false,
contentType:	false,
processData:	false,
complete: function()
{
$form.removeClass( 'is-uploading' );
},
success: function( data )
{
$form.addClass( data.success == true ? 'is-success' : 'is-error' );
if( !data.success ) $errorMsg.text( data.error );
},
error: function()
{
alert( 'Error. Please, contact the webmaster!' );
}
});
}
else // fallback Ajax solution upload for older browsers
{
var iframeName	= 'uploadiframe' + new Date().getTime(),
$iframe		= $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );
$( 'body' ).append( $iframe );
$form.attr( 'target', iframeName );
$iframe.one( 'load', function()
{
var data = $.parseJSON( $iframe.contents().find( 'body' ).text() );
$form.removeClass( 'is-uploading' ).addClass( data.success == true ? 'is-success' : 'is-error' ).removeAttr( 'target' );
if( !data.success ) $errorMsg.text( data.error );
$iframe.remove();
});
}
});
// restart the form if has a state of error/success
$restart.on( 'click', function( e )
{
e.preventDefault();
$form.removeClass( 'is-error is-success' );
$input.trigger( 'click' );
});
// Firefox focus bug fix for file input
$input
.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
});
})( jQuery, window, document );
</script>
-->
    <!--
NO-DEPENDENCIES (IE 10+)
-->
    <script>
      'use strict';
      ;
      ( function ( document, window, index )
       {
        // feature detection for drag&drop upload
        var isAdvancedUpload = function()
        {
          var div = document.createElement( 'div' );
          return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
        }
        ();
        // applying the effect for every form
        var forms = document.querySelectorAll( '.box' );
        Array.prototype.forEach.call( forms, function( form )
                                     {
          var input		 = form.querySelector( 'input[type="file"]' ),
              label		 = form.querySelector( 'label' ),
              errorMsg	 = form.querySelector( '.box__error span' ),
              restart		 = form.querySelectorAll( '.box__restart' ),
              droppedFiles = false,
              showFiles	 = function( files )
          {
            label.textContent = files.length > 1 ? ( input.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', files.length ) : files[ 0 ].name;
          }
          ,
              triggerFormSubmit = function()
          {
            var event = document.createEvent( 'HTMLEvents' );
            event.initEvent( 'submit', true, false );
            form.dispatchEvent( event );
          };
          // letting the server side to know we are going to make an Ajax request
          var ajaxFlag = document.createElement( 'input' );
          ajaxFlag.setAttribute( 'type', 'hidden' );
          ajaxFlag.setAttribute( 'name', 'ajax' );
          ajaxFlag.setAttribute( 'value', 1 );
          form.appendChild( ajaxFlag );
          // automatically submit the form on file select
          input.addEventListener( 'change', function( e )
                                 {
            showFiles( e.target.files );
            triggerFormSubmit();
          }
                                );
          // drag&drop files if the feature is available
          if( isAdvancedUpload )
          {
            form.classList.add( 'has-advanced-upload' );
            // letting the CSS part to know drag&drop is supported by the browser
            [ 'drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop' ].forEach( function( event )
                                                                                                     {
              form.addEventListener( event, function( e )
                                    {
                // preventing the unwanted behaviours
                e.preventDefault();
                e.stopPropagation();
              }
                                   );
            }
                                                                                                    );
            [ 'dragover', 'dragenter' ].forEach( function( event )
                                                {
              form.addEventListener( event, function()
                                    {
                form.classList.add( 'is-dragover' );
              }
                                   );
            }
                                               );
            [ 'dragleave', 'dragend', 'drop' ].forEach( function( event )
                                                       {
              form.addEventListener( event, function()
                                    {
                form.classList.remove( 'is-dragover' );
              }
                                   );
            }
                                                      );
            form.addEventListener( 'drop', function( e )
                                  {
              droppedFiles = e.dataTransfer.files;
              // the files that were dropped
              showFiles( droppedFiles );
              triggerFormSubmit();
            }
                                 );
          }
          // if the form was submitted
          form.addEventListener( 'submit', function( e )
                                {
            // preventing the duplicate submissions if the current one is in progress
            if( form.classList.contains( 'is-uploading' ) ) return false;
            form.classList.add( 'is-uploading' );
            form.classList.remove( 'is-error' );
            if( isAdvancedUpload ) // ajax file upload for modern browsers
            {
              e.preventDefault();
              // gathering the form data
              var ajaxData = new FormData( form );
              if( droppedFiles )
              {
                Array.prototype.forEach.call( droppedFiles, function( file )
                                             {
                  ajaxData.append( input.getAttribute( 'name' ), file );
                }
                                            );
              }
              // ajax request
              var ajax = new XMLHttpRequest();
              ajax.open( form.getAttribute( 'method' ), form.getAttribute( 'action' ), true );
              ajax.onload = function()
              {
                form.classList.remove( 'is-uploading' );
                if( ajax.status >= 200 && ajax.status < 400 )
                {
                  var data = JSON.parse( ajax.responseText );
                  form.classList.add( data.success == true ? 'is-success' : 'is-error' );
                  if( !data.success ) errorMsg.textContent = data.error;
                  if(data.success) {
                    var a = document.getElementById('successlink');
                    a.href = data.error;
                    a.textContent = data.error;
                  }
                }
                else alert( 'Error. Please, contact the webmaster!' );
              };
              ajax.onerror = function()
              {
                form.classList.remove( 'is-uploading' );
                alert( 'Error. Please, try again!' );
              };
              ajax.send( ajaxData );
            }
            else // fallback Ajax solution upload for older browsers
            {
              var iframeName	= 'uploadiframe' + new Date().getTime(),
                  iframe		= document.createElement( 'iframe' );
              $iframe		= $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );
              iframe.setAttribute( 'name', iframeName );
              iframe.style.display = 'none';
              document.body.appendChild( iframe );
              form.setAttribute( 'target', iframeName );
              iframe.addEventListener( 'load', function()
                                      {
                var data = JSON.parse( iframe.contentDocument.body.innerHTML );
                form.classList.remove( 'is-uploading' )
                form.classList.add( data.success == true ? 'is-success' : 'is-error' )
                form.removeAttribute( 'target' );
                if( !data.success ) errorMsg.textContent = data.error;
                iframe.parentNode.removeChild( iframe );
              }
                                     );
            }
          }
                               );
          // restart the form if has a state of error/success
          Array.prototype.forEach.call( restart, function( entry )
                                       {
            entry.addEventListener( 'click', function( e )
                                   {
              e.preventDefault();
              form.classList.remove( 'is-error', 'is-success' );
              input.click();
            }
                                  );
          }
                                      );
          // Firefox focus bug fix for file input
          input.addEventListener( 'focus', function(){
            input.classList.add( 'has-focus' );
          }
                                );
          input.addEventListener( 'blur', function(){
            input.classList.remove( 'has-focus' );
          }
                                );
        }
                                    );
      }
       ( document, window, 0 ));
    </script>
  </body>
</html>