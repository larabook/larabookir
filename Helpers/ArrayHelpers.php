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