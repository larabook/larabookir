<?php
namespace App\Larabookir;

abstract class Enum
{
	protected $consts = [], $attributes = [];

	protected $translations = [];

	protected static $singleton = [];

	public static function singelton()
	{

		$class = get_called_class();

		if (!isset(static::$singleton[$class])) {
			static::$singleton[$class] = (new $class);
		}

		return static::$singleton[$class];
	}

	function __construct()
	{

		$class = get_called_class();

		// Gets consts
		$reflect = new \ReflectionClass($class);
		$this->consts = $reflect->getConstants();


		// fill translation variable
		if (method_exists($this, 'translations'))
			$this->translations = (array)$this->translations();

		// fill csses
		if (method_exists($this, 'attributes'))
			$this->attributes = (array)$this->attributes();

	}

	/**
	 * Gets All list
	 * @return array
	 */
	public static function all()
	{
		$list = [];

		foreach (self::singelton()->consts as $name => $val) {
			$list[$name] = [
				'title' => self::singelton()->translate($val),
				'html' => self::singelton()->translate($val, true),
				'value' => $val
			];
		}

		return $list;
	}

	/**
	 * Gets All list except
	 * @param array $except
	 * @return array
	 */
	public static function except($except = [])
	{
		return array_except(self::all(), (array)$except);
	}

	/**
	 * Gets list only
	 * @param array $only
	 * @return array
	 */
	public static function only($only = [])
	{
		return array_only(self::all(), (array)$only);
	}

	/**
	 * @param $const
	 * @param bool|false $html
	 * @return mixed
	 */
	public static function getlabel($const, $html = false)
	{
		return self::singelton()->translate($const, $html);
	}

	/**
	 * Gets label list
	 * @param array $except
	 * @param bool|false $html
	 * @return array
	 */
	public static function getLabels($html = false)
	{
		$list = [];

		foreach (self::singelton()->consts as $name => $const)
			$list[$const] = self::getLabel($const, $html);

		return $list;
	}

	/**
	 * Gets label list Exceptional
	 * @param array $except
	 * @param bool|false $html
	 * @return array
	 */
	public static function getLabelsExcept($except = [], $html = false)
	{
		return array_except(self::getLabels($html), (array)$except);
	}

    /**
     * Gets the constants list mentioned in first parameter
     *
     * @param array $only
     * @param bool $html
     *
     * @return array
     * @internal param bool|false $style
     */
	public static function getLabelsOnly($only = [], $html = false)
	{
		return array_only(self::getLabels($html), (array)$only);
	}


	/**
	 * Gets constants list
	 * @return array
	 */
	public static function getConstants()
	{
		return array_keys(self::singelton()->consts);
	}

	/**
	 * Gets constants list
	 * @param array $except
	 * @return array
	 */
	public static function getConstantsExcept($except = [])
	{
		return array_keys(array_except(self::singelton()->consts, (array)$except));
	}

	/**
	 * Gets constans slug list
	 * @return array
	 */
	public static function getSlugs()
	{
		$list = [];

		foreach (self::getConstants() as $const)
			$list[$const] = str_uslug($const);

		return $list;
	}

	/**
	 * Gets constant slug list
	 * @param array $except
	 * @return array
	 */
	public static function getSlugsExcept($except = [])
	{
		return array_except(self::getSlugs(), (array)$except);
	}

	/**
	 * Finds constant related to given style
	 * @param $slug
	 * @return mixed
	 */
	public static function getConstBySlug($slug)
	{
		foreach (self::singelton()->consts as $name => $const) {
			if (str_uslug($const) == $slug)
				return $const;
		}
	}

	/**
	 * Checks whether the given constant exists
	 * @param $name
	 * @param bool|false $strict
	 * @return bool
	 */
	public static function exists($name, $strict = false)
	{
		$constants = self::singelton()->consts;

		if ($strict) {
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

    /**
     * Checks whether the given value exists
     * @return array
     * @internal param $value
     */
	public static function getValues()
	{
		return array_values(self::singelton()->consts);
	}

	/**
	 * Checks whether the given value exists
     * @return array
     * @internal param $value
	 */
	public static function getValuesExcept($except = [])
	{
		return array_except(self::getValues(), (array)$except);
	}

	/**
	 * Checks whether the given value exists
	 * @param $value
	* @return bool
	*/
	public static function valueExists($value)
	{
		return in_array($value, self::getValues(), true);
	}

	/**
	 * Translates constants
	 * @param $const
	 * @param bool|false $html
	 * @return null|string
	 */
	function translate($const, $html = false)
	{
		$label = null;

		if (isset($this->translations[$const]))
			$label = $this->translations[$const];

		if ($html) {

			// fill attributes
			$attrs = null;
			if (isset($this->attributes [$const]))
				$attrs = html_attributes($this->attributes[$const]);

			return "<label{$attrs}>$label</label>";
		}

		return $label;
	}
}