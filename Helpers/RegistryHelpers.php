<?php

if (!function_exists('getMeta')) {

	function getMeta($key = null)
	{
		if (!app()->offsetExists('_meta'))
			return;

		$meta = app()->_meta;

		switch ($key) {
			case 'title':
				$title = [];

				if (isset($meta['realm']))
					$title[] = $meta['realm'];

				if (isset($meta['title']))
					$title[] = $meta['title'];

				return join(' | ', $title);
				break;

			default:
				return isset($meta[$key]) ? $meta[$key] : (!isset($key) ? $meta : null);
		}

	}
}

if (!function_exists('hasMeta')) {

	function hasMeta($key)
	{
		if (!app()->offsetExists('_meta'))
			false;

		return isset(app()->_meta[$key]);
	}
}

if (!function_exists('setMeta')) {

	function setMeta($key)
	{
		$data = parse_args_assoc(func_get_args());

		app()->bind('_meta', function () use ($data) {
			return $data;
		});
	}
}


if (!function_exists('pushMeta')) {

	function pushMeta()
	{
		$data = parse_args_assoc(func_get_args());

		if (!app()->offsetExists('_meta'))
			return setMeta($data);

		app()->_meta = array_merge((array)app()->_meta, $data);
	}
}