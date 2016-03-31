<?php


if (!function_exists('array_diff_key_recursive')) {
	/**
	 * Computes the difference of arrays using keys for comparison (recursive version)
	 * @param array $arr1
	 * @param array $arr2
	 * @return array
	 */
	function array_diff_key_recursive(array $arr1, array $arr2)
	{
		$diff = array_diff_key($arr1, $arr2);
		$intersect = array_intersect_key($arr1, $arr2);

		foreach ($intersect as $k => $v) {
			if (is_array($arr1[$k]) && is_array($arr2[$k])) {
				$d = array_diff_key_recursive($arr1[$k], $arr2[$k]);

				if ($d) {
					$diff[$k] = $d;
				}
			}
		}
		return $diff;
	}
}


if (!function_exists('to_array')) {
	/**
	 * convert object to array
	 * @param $value
	 * @return array
	 */
	function to_array($value)
	{
		if (is_object($value) && method_exists($value, 'toArray'))
			$value = $value->toArray();
		return (array)$value;
	}
}

if (!function_exists('array_undot')) {
	/**
	 * Collapse the given dots array to associative array
	 * @param $array
	 * @return array
	 */
	function array_undot($array)
	{
		$results = array();

		foreach ($array as $key => $value) {
			array_set($results, $key, $value);
		}

		return $results;
	}
}


if (!function_exists('is_first')) {
	/**
	 * chcek whether the given value is the first item  of given Array
	 * @param $value
	 * @param $Array
	 */
	function is_first($value, $array)
	{
		return $value ==collect($array)->first(function ($k, $v) {
			return true;
		});
	}
}

if (!function_exists('is_last')) {
	/**
	 * chcek whether the given value is the last item  of given Array
	 * @param $value
	 * @param $Array
	 */
	function is_last($value, $array)
	{
		return $value ==collect($array)->reverse()->first(function ($k, $v) {
			return true;
		});
	}

}