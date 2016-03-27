<?php

if (!function_exists('html_attributes')) {
	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	function html_attributes($attributes)
	{

		$html = [];

		foreach ((array)$attributes as $key => $value) {
			if (is_numeric($key))
				$key = $value;

			if (!is_null($value)) {
				$html[] = $key . '="' . e($value) . '"';
			}
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}
}

if (!function_exists('is_active')) {
	/**
	 * Check the request if it matches to pattern then will return string
	 * example : isActive(‘menu1’)
	 * @return stirng
	 */
	function is_active($pattern = null, $include_class = false)
	{
		return ((Request::is($pattern)) ? (($include_class) ? 'class="active"' : 'active') : '');
	}
}



if (!function_exists('fix_rtl')) {
	/**
	 * fixes text content align
	 * @param $string
	 * @return string
	 */
	function fix_rtl($string)
	{
		return "<span style='direction:rtl;display: inline-flex'>{$string}</span> ";
	}

}
if (!function_exists('fix_ltr')) {
	/**
	 * fixes text content align
	 * @param $string
	 * @return string
	 */
	function fix_ltr($string)
	{
		return "<span style='direction:ltr;display: inline-flex'>{$string}</span> ";
	}

}