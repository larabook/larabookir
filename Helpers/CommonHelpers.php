<?php

if (!function_exists('is_md5')) {
	/**
	 * Check if md5
	 * @return bool
	 */
	function is_md5($md5)
	{
		return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);
	}
}

if (!function_exists('rmkdir')) {
	/**
	 * Make directory recursive
	 * @param $dir
	 */

	function rmkdir($path, $mode = 0777)
	{
		$path = trim($path);
		$path = rtrim(preg_replace(array("/\\\{2,}/", "/\/{2,}/"), "/", $path), "/");
		if (substr($path, 1, 1) == ":") {
			//win
			$s = substr($path, 0, 2);
			$path = substr($path, 3);
		} else {
			$s = null;
			$path = ltrim($path, './');
		}

		$dirs = explode('/', $path);
		$count = count($dirs);

		$oldmask = umask(0);
		for ($i = 0; $i < $count; ++$i) {
			$s .= '/' . $dirs[$i];
			if (@file_exists($s) || !@mkdir($s, $mode)) {
				continue;
			}
			@chmod($s, $mode);
		}
		umask($oldmask);
		return true;
	}
}


if (!function_exists('is_localhost')) {
	/**
	 * check wheather is local server or not
	 * @return bool
	 */
	function is_localhost()
	{
		if (in_array($_SERVER['REMOTE_ADDR'], ['localhost', '127.0.0.1', '::1']))
			return true;
		return false;
	}
}

if (!function_exists('is_base64')) {
	/**
	 * check if is base64
	 * @param $data
	 * @return bool
	 */
	function is_base64($data)
	{
		if (preg_match("/^[a-zA-Z0-9\!\-_]+$/", $data))
			return true;
		return false;
	}
}


if (!function_exists('is_serialized')) {
	/**
	 * check if serialized
	 * @param $str
	 * @return bool
	 */
	function is_serialized($str)
	{
		return ($str == serialize(false) || @unserialize($str) !== false);
	}
}
if (!function_exists('ddd')) {
	/**
	 * Debug data without stoping
	 */
	function ddd()
	{
		echo "<style> div>.sf-dump{ margin: 0; } </style>";
		echo "<div style='direction: ltr; background: #E9900A none repeat scroll 0% 0% !important; padding: 4px 6px 4px 6px;margin:0;'>";
		array_map(function ($x) {
			(new \Illuminate\Support\Debug\Dumper())->dump($x);
		}, func_get_args());
		echo "</div>";
	}
}

if (!function_exists('dd_next_query')) {
	function dd_next_query()
	{
		\DB::listen(function ($sql, $bindings, $time) {
			if ($bindings) {
				$pdo = DB::getPdo();
				foreach ($bindings as $binding)
					$esql = preg_replace('/\?/', $pdo->quote($binding), $sql, 1);

				$res = (object)[
					'sql' => $sql,
					'bindingss' => $bindings,
					'executed_sql' => $esql,
					'time' => $time,
				];
			} else {
				$res = (object)[
					'sql' => $sql,
					'time' => $time,
				];
			}
			dd($res);
		});
	}
}


if (!function_exists('parse_args')) {
	/**
	 * Parsing Sequential arguments
	 * @param $args
	 * @return array
	 */

	function parse_args($args)
	{
		if (!$args)
			return [];
		if (is_array($args[0]))
			return $args[0];
		else
			return $args;
	}
}


if (!function_exists('parse_argse_assoc')) {
	/**
	 * Parsing associative arguments
	 * @param $args
	 * @return array
	 */
	function parse_argse_assoc($args)
	{
		if (isset($args[0]) && isset($args[1]) && !is_array($args[0])) {
			$args[$args[0]] = $args[1];
			unset($args[0]);
			unset($args[1]);
		} else { // is array
			$args = $args[0];
		}
		return $args;
	}
}


if (!function_exists('posts_all')) {
	/**
	 * Get all posts
	 * @return array
	 */
	function posts_all()
	{
		$args = parse_args(func_get_args());
		if ($args)
			$all =  $request->only($args);
		else
			$all =  $request->all();

		$queries =  $request->query();
		return array_diff_key_recursive($all, $queries);
	}
}


if (!function_exists('posts_except')) {

	function posts_except()
	{
		$request=app('request');
		$args = parse_args(func_get_args());
		if ($args)
			$all = $request->except($args);
		else
			$all = $request->input();

		$queries =  $request->query();
		return array_diff_key_recursive($all, $queries);
	}

}


if (!function_exists('left_ch')) {
	/**
	 * Removes tralin char and add one left
	 * @param $dir
	 * @return string
	 */
	function left_ch($dir, $ch = '/')
	{
		$trimed = ltrim($dir, $ch);
		if (!empty($trimed))
			return $ch . $trimed;
	}
}


if (!function_exists('right_ch')) {
	/**
	 * Removes tralin char and add one right
	 * @param $dir
	 * @return string
	 */
	function right_ch($dir, $ch = '/')
	{
		$trimed = rtrim($dir, $ch);
		if (!empty($trimed))
			return $trimed . $ch;
	}
}

if (!function_exists('int_random')) {
	/**
	 * Generate a more truly "random" numeric string.
	 *
	 * @param  int $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	function int_random($length = 11)
	{
		$a = null;
		for ($i = 0; $i < $length; $i++) {
			if ($length > 1 && $i == 0)
				$a .= mt_rand(1, 9);
			else
				$a .= mt_rand(0, 9);
		}
		return $a;
	}
}

if (!function_exists('controller_name')) {
	/**
	 * returns the current action name
	 * @return string
	 */
	function controller_name()
	{
		list($controller) = explode('@', Route::getCurrentRoute()->getActionName());
		$controller = explode('\\', $controller);
		return end($controller);
	}
}


if (!function_exists('action_name')) {
	/**
	 * returns the current action name
	 * @return string
	 */
	function action_name()
	{
		list(, $action) = explode('@', \Route::getCurrentRoute()->getActionName());
		return $action;
	}
}


if (!function_exists('is_editing')) {

	function is_editing()
	{
		return action_name() == 'edit';
	}
}

if (!function_exists('is_creating')) {

	function is_creating()
	{
		return action_name() == 'create';
	}
}

if (!function_exists('is_first')) {
	/**
	 * chcek whether the given key is the first index  of given Array
	 * @param $key
	 * @param $Array
	 */
	function is_first($key, $array)
	{
		static $first_key;
		if (is_object($array)) {
			if (method_exists($array, 'toArray'))
				$array = $array->toArray();
			else
				$array = (array)$array;
		}
		if (!is_array($array))
			return false;
		if (!isset($first_key)) {
			reset($array);
			$first_key = key($array);
		}
		return $key == $first_key;
	}
}


if (!function_exists('is_last')) {
	/**
	 * chcek whether the given key is the last index  of given Array
	 * @param $key
	 * @param $Array
	 */
	function is_last($key, $array)
	{
		static $last_key;
		if (is_object($array)) {
			if (method_exists($array, 'toArray'))
				$array = $array->toArray();
			else
				$array = (array)$array;
		}
		if (!is_array($array))
			return false;
		if (!isset($last_key)) {
			end($array);
			$last_key = key($array);
		}
		return $key == $last_key;
	}

}


if (!function_exists('get_client_ip')) {
	/**
	 * Get IP
	 * @return string|null
	 */
	function get_client_ip()
	{
		static $ip;
		if (isset($ip))
			return $ip;
		if (in_array($_SERVER['REMOTE_ADDR'], ['localhost', '127.0.0.1', '::1']))
			return "localhost";
		$ip = null;
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_X_FORWARDED']))
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_FORWARDED']))
			$ip = $_SERVER['HTTP_FORWARDED'];
		else if (isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}

}


if (!function_exists('gen_uuid')) {
	/**
	 * Generate universal unique ID
	 * @return string
	 */
	function gen_uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

}


if (!function_exists('upload_img')) {
	/**
	 * Resize image on fly on save it to given path
	 * @param $maxwidth
	 * @param $maxheight
	 * @param int $quality
	 * quality is optional, and ranges from 0 (worst
	 * quality, smaller file) to 100 (best quality, biggest file). The
	 * default is the default IJG quality value (about 75).
	 * @param $dir
	 * @param $image
	 * @return bool
	 */
	function upload_img($maxwidth, $maxheight, $quality, $dir, $image)
	{

		$msg = false;

		if ($dir != "" && $image != "") {
			$size = getimagesize("$image");

			$width_1 = $size[0];
			$height_1 = $size[1];

			if ($height_1 <= $maxheight || $width_1 <= $maxwidth) {
				$n_width = $width_1;
				$n_height = $height_1;
			}

			if ($height_1 > $maxheight) {
				$n_height = $maxheight;
				$percent = ($size[1] / $n_height);
				$n_width = ($size[0] / $percent);
			} else if ($width_1 > $maxwidth) {
				$n_width = $maxwidth;
				$percent = ($size[0] / $n_width);
				$n_height = ($size[1] / $percent);
			}

			if ($n_height > $maxheight) {
				$n_height = $maxheight;
				$percent = ($size[1] / $n_height);
				$n_width = ($size[0] / $percent);
			} else if ($n_width > $maxwidth) {
				$n_width = $maxwidth;
				$percent = ($size[0] / $n_width);
				$n_height = ($size[1] / $percent);
			}

			$image_p = imagecreatetruecolor($n_width, $n_height);
			$image_q = imagecreatefromjpeg($image);
			imagecopyresampled($image_p, $image_q, 0, 0, 0, 0, $n_width, $n_height, $width_1, $height_1);

			if (imagejpeg($image_p, $dir, $quality)) {
				$msg = true;
			} else {
				$msg = false;
			}
		}

		return $msg;

	}
}


if (!function_exists('str_uslug')) {
	/**
	 * Generate a unicode URL friendly "slug" from a given string
	 * @param $string
	 * @return mixed|string
	 */
	function str_uslug($string)
	{
		$LNSH = '/[^\_\-\s\pN\pL]+/u';
		$SADH = '/[\_\-\s]+/';

		$string = preg_replace($LNSH, '', mb_strtolower($string, 'UTF-8'));
		$string = preg_replace($SADH, '-', $string);
		$string = trim($string, '-');

		return $string;
	}
}


if (!function_exists('str_unique_uslug')) {
	/**
	 * Generate a unique and  unicode URL friendly "slug" from a given string
	 *
	 * @param $title
	 * @param $model
	 * @return mixed|string
	 */
	function str_unique_uslug($title, $model)
	{
		$slug = str_uslug($title);
		$slugCount = count($model->whereRaw("slug REGEXP '^{$slug}(-[0-9]*)?$'")->get());

		return ($slugCount > 0) ? "{$slug}-{$slugCount}" : $slug;
	}
}


if (!function_exists('fix_persian_num')) {
	/**
	 * convert the given number to persian
	 * @param $text
	 * @return null|string
	 */
	function fix_persian_num($text)
	{

		if (is_null($text)) {
			return null;
		}
		$replacePairs = array(
			"0" => chr(0xDB) . chr(0xB0),
			"1" => chr(0xDB) . chr(0xB1),
			"2" => chr(0xDB) . chr(0xB2),
			"3" => chr(0xDB) . chr(0xB3),
			"4" => chr(0xDB) . chr(0xB4),
			"5" => chr(0xDB) . chr(0xB5),
			"6" => chr(0xDB) . chr(0xB6),
			"7" => chr(0xDB) . chr(0xB7),
			"8" => chr(0xDB) . chr(0xB8),
			"9" => chr(0xDB) . chr(0xB9),
		);
		return strtr($text, $replacePairs);

	}
}


if (!function_exists('validation_state')) {
	/**
	 * validation state helper
	 *
	 * @param \Illuminate\Support\ViewErrorBag $errors
	 * @param array|string $names
	 * @param string $context
	 * @return string
	 */
	function validation_state(Illuminate\Support\ViewErrorBag $errors, $names, $context = 'has-danger')
	{
		//normalize input to array
		if (!is_array($names)) {
			$names = [$names];
		}
		//check if error exists
		foreach ($names as $name) {
			if ($errors->has($name)) {
				return $context;
			}
		}
		//no error
		return '';
	}
}

