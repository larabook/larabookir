<?php namespace Larabookir\Carbon;

class jCarbon extends Carbon
{
	const MODE_JALALI = 'jalali';
	const MODE_GREGORIAN = 'gregorian';


	/**
	 * Default format to use for __toString method when type juggling occurs.
	 *
	 * @var string
	 */
	const DEFAULT_TO_STRING_FORMAT = 'Y/m/d';

	/**
	 * Format to use for __toString method when type juggling occurs.
	 *
	 * @var string
	 */
	protected static $toStringFormat = self::DEFAULT_TO_STRING_FORMAT;

	/**
	 * The day constants.
	 */
	const SATURDAY = 0;
	const SUNDAY = 1;
	const MONDAY = 2;
	const TUESDAY = 3;
	const WEDNESDAY = 4;
	const THURSDAY = 5;
	const FRIDAY = 6;

	/**
	 * Names of days of the week.
	 *
	 * @var array
	 */
	protected static $days = [
		self::SATURDAY  => 'Saturday',
		self::SUNDAY    => 'Sunday',
		self::MONDAY    => 'Monday',
		self::TUESDAY   => 'Tuesday',
		self::WEDNESDAY => 'Wednesday',
		self::THURSDAY  => 'Thursday',
		self::FRIDAY    => 'Friday',
	];

	/**
	 * First day of week.
	 *
	 * @var int
	 */
	protected static $weekStartsAt = self::SATURDAY;

	/**
	 * Last day of week.
	 *
	 * @var int
	 */
	protected static $weekEndsAt = self::FRIDAY;

	/**
	 * Days of weekend.
	 *
	 * @var array
	 */
	protected static $weekendDays = [
		self::FRIDAY,
	];

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param  string $method
	 * @param  array $args
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	/*    public static function __callStatic($method, $args)
		{
		   if(method_exists(get_called_class(),$method))
			   call_user_func_array([])
		}*/

	const FARSI_DIGITS = 'farsi';
	const LATIN_DIGITS = 'latin';
	/**
	 * Default Digits type. (e.g Farsi or Latin)
	 *
	 * @var array
	 */
	protected static $defaultDigitsType = self::LATIN_DIGITS;


	public static function digitsType($mode)
	{
		if (in_array($mode, [self::FARSI_DIGITS, self::LATIN_DIGITS]))
			self::$defaultDigitsType = $mode;
	}


	/**
	 * Create a new Carbon instance from a specific date and time.
	 *
	 * If any of $year, $month or $day are set to null their now() values will
	 * be used.
	 *
	 * If $hour is null it will be set to its now() value and the default
	 * values for $minute and $second will be their now() values.
	 *
	 * If $hour is not null then the default values for $minute and $second
	 * will be 0.
	 *
	 * @param int|null $year
	 * @param int|null $month
	 * @param int|null $day
	 * @param int|null $hour
	 * @param int|null $minute
	 * @param int|null $second
	 * @param \DateTimeZone|string|null $tz
	 *
	 * @return static
	 */
	public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $tz = null, $mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::create($year, $month, $day, $hour, $minute, $second, $tz);
		}

		$copy = (new static)->now();

		$year = $year === null ? $copy->year : $year;
		$month = $month === null ? $copy->month : $month;
		$day = $day === null ? $copy->day : $day;

		if ($hour === null) {
			$hour = date('G');
			$minute = $minute === null ? date('i') : $minute;
			$second = $second === null ? date('s') : $second;
		} else {
			$minute = $minute === null ? 0 : $minute;
			$second = $second === null ? 0 : $second;
		}

		list($gy, $gm, $gd) = self::to_gregorian($year, $month, $day);

		return parent::createFromFormat('Y-n-j G:i:s', sprintf('%s-%s-%s %s:%02s:%02s', $gy, $gm, $gd, $hour, $minute, $second), $tz);
	}


	/**
	 * Create a Carbon instance from just a date. The time portion is set to now.
	 *
	 * @param int|null $year
	 * @param int|null $month
	 * @param int|null $day
	 * @param \DateTimeZone|string|null $tz
	 *
	 * @return static
	 */
	public static function createFromDate($year = null, $month = null, $day = null, $tz = null, $mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::create($year, $month, $day, null, null, null, $tz);
		}
		return static::create($year, $month, $day, null, null, null, $tz);
	}

	/**
	 * Create a Carbon instance from a specific format.
	 *
	 * @param string $format
	 * @param string $time
	 * @param \DateTimeZone|string|null $tz
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return static
	 * @TODO
	 */
	/*
	public static function createFromFormat($format, $time, $tz = null)
	{
	}
	*/

	/**
	 * Sets current DataTime object to the given jalali date.
	 * Calls modify as a workaround for a php bug
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 *
	 * @return Carbon
	 */
	public function setDate($year, $month, $day, $mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN) {
			return parent::setDate($year, $month, $day);
		}

		list($gy, $gm, $gd) = self::to_gregorian($year, $month, $day);
		return parent::setDate($gy, $gm, $gd);
	}

	/**
	 * Sets current DataTime object to the given jalali date and time.
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param int $hour
	 * @param int $minute
	 * @param int $second
	 *
	 * @return static
	 */
	public function setDateTime($year, $month, $day, $hour, $minute, $second = 0, $mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN) {
			return parent::setDateTime($gy, $gm, $gd, $hour, $minute, $second);
		}
		list($gy, $gm, $gd) = self::to_gregorian($year, $month, $day);
		return parent::setDate($gy, $gm, $gd)->setTime($hour, $minute, $second);
	}

	public static function called_by($file, $func)
	{
		foreach (debug_backtrace() as $trace) {
			if ($trace['function'] == $func && basename($trace['file']) == $file)
				return true;
		}
		return false;
	}


	public static function called_by_parent($func)
	{
		$map = (array)explode('\\', get_parent_class());
		$file = end($map) . '.php';
		return static::called_by($file, $func);
	}

	/**
	 * Returns date formatted according to given format.
	 *
	 * @param string $format
	 * @param string $digits
	 *
	 * @return mixed|null|string
	 */
	public function format($format = self::DEFAULT_TO_STRING_FORMAT, $mode = self::MODE_JALALI, $digits = null)
	{
		// در صورتی که از درون کلاس والد صدا زده شده باشد
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}

		$timestamp = parent::getTimestamp();
		list($gy, $gm, $gd) = explode('-', date('Y-m-d', $timestamp));
		list($jy, $jm, $jd) = self::to_jalali($gy, $gm, $gd);

		$i = 0;
		$lastchar = null;
		$out = null;
		while (($ch = substr($format, $i, 1)) !== false) {
			//--unformat chars
			if ($ch == '\\') {
				$out .= substr($format, $i + 1, 1);
				$i += 2;
				continue;
			}

			//Intact formats
			if (in_array($ch, ['B', 'h', 'H', 'g', 'G', 'i', 's', 'I', 'U', 'u', 'Z', 'O', 'P'])) {
				$out .= date($ch, $timestamp);
			} else {
				switch ($ch) {
					case 'A':
						if (date('A', $timestamp) == 'PM')
							$out .= 'بعد از ظهر';
						else
							$out .= 'قبل از ظهر';
						break;
					case 'a':
						if (date('A', $timestamp) == 'PM')
							$out .= "ب.ظ";
						else
							$out .= "ق.ظ";
						break;
					case 'd':
						$out .= sprintf('%02d', $jd); // day
						break;
					case 'D':
						$out .= $this->to_persian_weekday(date('w', $timestamp), true);// Persian Shorted day of week ex:  ش
						break;
					case 'F':
						$out .= $this->to_persian_month($jm);// Persian Month ex: فروردین
						break;

					case "j":
						$out .= intval($jd);// intval(day)
						break;
					case "l":
						$out .= $this->to_persian_weekday(date('w', $timestamp), false);// Persian  day of week ex:  شنبه
						break;
					case "m":
						$out .= sprintf('%02d', $jm); // month
						break;
					case "M":
						$out .= $this->to_persian_month($jm, true); // Persian  Shorted Month ex : فرو , ارد
						break;
					case "n":
						$out .= intval($jm); // intval(month)
						break;
					case "N":
						$jd_of_week = [7 => 2, 1 => 3, 2 => 4, 3 => 5, 4 => 6, 5 => 7, 6 => 1];
						$out .= $jd_of_week[ date("N", $timestamp) ]; // day of week
						break;
					case "L":
						$out .= $this->is_leap_year($jy) ? 1 : 0; // year is Leap (Kabiseh)
						break;
					case "S":
						$out .= 'ام';
						break;
					case "t":
						$is_leap = $this->is_leap_year($jy) ? 1 : 0;
						if ($jm <= 6)
							$jds_in_month = 31;
						else if ($jm > 6 && $jm < 12)
							$jds_in_month = 30;
						else if ($jm == 12)
							$jds_in_month = $is_leap ? 30 : 29;
						$out .= $jds_in_month; // last day of month
						break;
					case "w":
						$jd_of_week = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
						$out .= $jd_of_week[ date("w", $timestamp) ]; // day of week
						break;
					case "W":
						$daysOFLastWeek = intval((new static)->setDate($jy, 1, 1)->format('w', self::MODE_JALALI, self::LATIN_DIGITS)); // تعداد روزهای آخرین هفته قبل از سال نو
						$out .= ceil(($this->day_of_year($jm, $jd) + $daysOFLastWeek) / 7); // number of weeks
						break;
					case "y":
						$out .= substr($jy, 2); // short year ex : 1391  =>  91
						break;
					case "Y":
						$out .= $jy; // Full Year ex : 1391
						break;
					case "z":
						$out .= $this->day_of_year($jm, $jd); // the day of the year ex: 280  or 365
						break;

					default :
						$out .= $ch;
				}
			}
			$i++;
		}
		if (is_null($digits))
			$digits = self::$defaultDigitsType;
		if ($digits == self::FARSI_DIGITS)
			$out = self::to_digits($out, self::FARSI_DIGITS, '.');
		/*	if(self::$rtl)
				$out = self::fixRTLAlign($out);*/
		return $out;
	}

	///////////////////////////////////////////////////////////////////
	///////////////////////// GETTERS AND SETTERS /////////////////////
	///////////////////////////////////////////////////////////////////

	/**
	 * Get a part of the Carbon object
	 *
	 * @param string $name
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string|int|\DateTimeZone
	 */
	public function __get($name)
	{
		switch (true) {
			case array_key_exists($name, $formats = [
				'year'        => 'Y',
				'yearIso'     => 'o',
				'month'       => 'n',
				'day'         => 'j',
				'hour'        => 'G',
				'minute'      => 'i',
				'second'      => 's',
				'micro'       => 'u',
				'dayOfWeek'   => 'w',
				'dayOfYear'   => 'z',
				'weekOfYear'  => 'W',
				'daysInMonth' => 't',
				'timestamp'   => 'U',
			]):
				return (int)$this->format($formats[ $name ]);

			case $name === 'weekOfMonth':
				return (int)ceil($this->day / static::DAYS_PER_WEEK);

			case $name === 'age':
				return (int)$this->diffInYears();

			case $name === 'quarter':
				return (int)ceil($this->month / 3);

			case $name === 'offset':
				return $this->getOffset();

			case $name === 'offsetHours':
				return $this->getOffset() / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR;

			case $name === 'dst':
				return $this->format('I') === '1';

			case $name === 'local':
				return $this->offset === $this->copy()->setTimezone(date_default_timezone_get())->offset;

			case $name === 'utc':
				return $this->offset === 0;

			case $name === 'timezone' || $name === 'tz':
				return $this->getTimezone();

			case $name === 'timezoneName' || $name === 'tzName':
				return $this->getTimezone()->getName();

			default:
				throw new InvalidArgumentException(sprintf("Unknown getter '%s'", $name));
		}
	}

	///////////////////////////////////////////////////////////////////
	/////////////////////// STRING FORMATTING /////////////////////////
	///////////////////////////////////////////////////////////////////

	/**
	 * Reset the format used to the default when type juggling a Carbon instance to a string
	 */
	public static function resetToStringFormat()
	{
		static::setToStringFormat(static::DEFAULT_TO_STRING_FORMAT);
	}

	/**
	 * Set the default format used when type juggling a Carbon instance to a string
	 *
	 * @param string $format
	 */
	public static function setToStringFormat($format)
	{
		static::$toStringFormat = $format;
	}

	/**
	 * Format the instance as a string using the set format
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->format(static::$toStringFormat);
	}

	/**
	 * Format the instance as date
	 *
	 * @return string
	 */
	public function toDateString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format('Y-m-d');
	}

	/**
	 * Format the instance as a readable date
	 *
	 * @return string
	 */
	public function toFormattedDateString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format('M j, Y');
	}

	/**
	 * Format the instance as time
	 *
	 * @return string
	 */
	public function toTimeString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format('H:i:s');
	}

	/**
	 * Format the instance as date and time
	 *
	 * @return string
	 */
	public function toDateTimeString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format('Y-m-d H:i:s');
	}

	/**
	 * Format the instance with day, date and time
	 *
	 * @return string
	 */
	public function toDayDateTimeString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format('D, M j, Y g:i A');
	}

	/**
	 * Format the instance as ATOM
	 *
	 * @return string
	 */
	public function toAtomString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::ATOM);
	}

	/**
	 * Format the instance as COOKIE
	 *
	 * @return string
	 */
	public function toCookieString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::COOKIE);
	}

	/**
	 * Format the instance as ISO8601
	 *
	 * @return string
	 */
	public function toIso8601String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->toAtomString();
	}

	/**
	 * Format the instance as RFC822
	 *
	 * @return string
	 */
	public function toRfc822String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC822);
	}

	/**
	 * Format the instance as RFC850
	 *
	 * @return string
	 */
	public function toRfc850String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC850);
	}

	/**
	 * Format the instance as RFC1036
	 *
	 * @return string
	 */
	public function toRfc1036String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC1036);
	}

	/**
	 * Format the instance as RFC1123
	 *
	 * @return string
	 */
	public function toRfc1123String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC1123);
	}

	/**
	 * Format the instance as RFC2822
	 *
	 * @return string
	 */
	public function toRfc2822String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC2822);
	}

	/**
	 * Format the instance as RFC3339
	 *
	 * @return string
	 */
	public function toRfc3339String($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RFC3339);
	}

	/**
	 * Format the instance as RSS
	 *
	 * @return string
	 */
	public function toRssString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::RSS);
	}

	/**
	 * Format the instance as W3C
	 *
	 * @return string
	 */
	public function toW3cString($mode = self::MODE_JALALI)
	{
		if ($mode == self::MODE_GREGORIAN || static::called_by_parent(__FUNCTION__)) {
			return parent::format($format);
		}
		return $this->format(static::W3C);
	}


	///////////////////////////////////////////////////////////////////
	/////////////////// ADDITIONS AND SUBTRACTIONS ////////////////////
	///////////////////////////////////////////////////////////////////
	/**
	 * Add months to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function addMonths($value)
	{
		$i = 0;

		if ($value > 0) {
			while ($i++ < $value) {
				if ($this->month == 12) {
					$this->month = 1;
					$this->year++;
				} else
					$this->month++;

			}
		} elseif ($value < 0) {
			while ($i++ < abs($value)) {
				if ($this->month == 1) {
					$this->month = 12;
					$this->year--;
				} else
					$this->month--;
			}
		}
		return $this;
	}


	/**
	 * Add weekdays to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function addWeekdays($value)
	{
		// fix for https://bugs.php.net/bug.php?id=54909
		$i = 0;

		if ($value > 0) {
			while ($i++ < $value) {
				if ($this->dayOfWeek == self::THURSDAY)
					$this->day += 2;
				else
					$this->day++;

			}
		} elseif ($value < 0) {
			while ($i++ < abs($value)) {
				if ($this->dayOfWeek == self::SATURDAY)
					$this->day -= 2;
				else
					$this->day--;
			}
		}
		return $this;
	}


	/**
	 * Add months without overflowing to the instance. Positive $value
	 * travels forward while negative $value travels into the past.
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function addMonthsNoOverflow($value)
	{
		$date = $this->copy()->addMonths($value);

		if ($date->day !== $this->day) {
			$date->day(1)->subMonth()->day($date->daysInMonth);
		}

		return $date;
	}


	///////////////////////////////////////////////////////////////////
	///////////////////////  Jalali Methods   /////////////////////////
	///////////////////////////////////////////////////////////////////

	/**
	 * Default Digits type. (e.g Farsi or Latin)
	 *
	 * @var array
	 */
	protected static $rtl = false;


	public static function rtl($mode = true)
	{
		self::$rtl = (bool)$mode;
	}


	public static function fixRTLAlign($string)
	{
		return "<div style=\"display:inline-block;\"><span style=\"direction:rtl; text-align:right; float: right;\">{$string}</span></div>";
	}

	/**
	 * تابع تبدل تاریخ میلادی به جلالی
	 * Authors : Roozbeh Pournader and Mohammad Toosi
	 *
	 * @param $g_year
	 * @param $g_month
	 * @param $g_day
	 *
	 * @return array خروجی آرایه به صورت تاریخ جلالی
	 */
	public static function to_jalali($g_year, $g_month, $g_day)
	{
		$g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		$j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
		$gy = $g_year - 1600;
		$gm = $g_month - 1;
		$gd = $g_day - 1;
		$g_day_no = 365 * $gy + self::div($gy + 3, 4) - self::div($gy + 99, 100) + self::div($gy + 399, 400);
		for ($i = 0; $i < $gm; ++$i)
			$g_day_no += $g_days_in_month[ $i ];
		if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
			$g_day_no++; /* leap and after Feb */
		$g_day_no += $gd;
		$j_day_no = $g_day_no - 79;
		$j_np = self::div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */
		$j_day_no = $j_day_no % 12053;
		$jy = 979 + 33 * $j_np + 4 * self::div($j_day_no, 1461); /* 1461 = 365*4 + 4/4 */
		$j_day_no %= 1461;
		if ($j_day_no >= 366) {
			$jy += self::div($j_day_no - 1, 365);
			$j_day_no = ($j_day_no - 1) % 365;
		}
		for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[ $i ]; ++$i)
			$j_day_no -= $j_days_in_month[ $i ];
		$jm = $i + 1;
		$jd = $j_day_no + 1;
		return [$jy, $jm, $jd];
	}

	/**
	 * تابع تبدیل تاریخ جلالی به میلادی
	 * Authors : Roozbeh Pournader and Mohammad Toosi
	 *
	 * @param $j_year
	 * @param $j_month
	 * @param $j_day
	 *
	 * @return array خروجی آرایه به صورت تاریخ میلادی
	 */
	public static function to_gregorian($j_year, $j_month, $j_day)
	{
		$g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		$j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
		$jy = $j_year - 979;
		$jm = $j_month - 1;
		$jd = $j_day - 1;
		$j_day_no = 365 * $jy + self::div($jy, 33) * 8 + self::div($jy % 33 + 3, 4);
		for ($i = 0; $i < $jm; ++$i)
			$j_day_no += $j_days_in_month[ $i ];
		$j_day_no += $jd;
		$g_day_no = $j_day_no + 79;
		$gy = 1600 + 400 * self::div($g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
		$g_day_no = $g_day_no % 146097;
		$leap = true;
		if ($g_day_no >= 36525) { /* 36525 = 365*100 + 100/4 */
			$g_day_no--;
			$gy += 100 * self::div($g_day_no, 36524); /* 36524 = 365*100 + 100/4 - 100/100 */
			$g_day_no = $g_day_no % 36524;
			if ($g_day_no >= 365)
				$g_day_no++;
			else
				$leap = false;
		}
		$gy += 4 * self::div($g_day_no, 1461); /* 1461 = 365*4 + 4/4 */
		$g_day_no %= 1461;
		if ($g_day_no >= 366) {
			$leap = false;
			$g_day_no--;
			$gy += self::div($g_day_no, 365);
			$g_day_no = $g_day_no % 365;
		}
		for ($i = 0; $g_day_no >= $g_days_in_month[ $i ] + ($i == 1 && $leap); $i++)
			$g_day_no -= $g_days_in_month[ $i ] + ($i == 1 && $leap);
		$gm = $i + 1;
		$gd = $g_day_no + 1;
		return [$gy, $gm, $gd];
	}

	/**
	 * return result of division two numbers
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	private static function div($a, $b)
	{
		return (int)($a / $b);
	}

	/**
	 * convert to persian number vise versa
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	private static function to_digits($str, $digits = self::LATIN_DIGITS, $jalali_float_symbol = '٫')
	{
		$num_a = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
		$key_a = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', $jalali_float_symbol];
		return ($digits == self::FARSI_DIGITS) ? str_replace($num_a, $key_a, $str) : str_replace($key_a, $num_a, $str);
	}

	/**
	 * return name of weekday
	 *
	 * @param $day
	 * @param bool|false $tiny
	 *
	 * @return string
	 */
	private function to_persian_weekday($day, $tiny = false)
	{
		switch ($day) {
			case 6:
				if ($tiny)
					return 'ش';
				else
					return 'شنبه';
				break;
			case 0:
				if ($tiny)
					return 'ی';
				else
					return 'يكشنبه';
				break;
			case 1:
				if ($tiny)
					return 'د';
				else
					return 'دوشنبه';
				break;
			case 2:
				if ($tiny)
					return 'س';
				else
					return 'سه شنبه';
				break;
			case 3:
				if ($tiny)
					return 'چ';
				else
					return 'چهارشنبه';
				break;
			case 4:
				if ($tiny)
					return 'پ';
				else
					return 'پنجشنبه';
				break;
			case 5:
				if ($tiny)
					return 'ج';
				else
					return 'جمعه';
				break;
		}
	}

	/**
	 * return name of month number
	 *
	 * @param $month
	 * @param bool|false $tiny
	 *
	 * @return string
	 */
	private function to_persian_month($month, $tiny = false)
	{
		switch ($month) {
			case 1:
				if ($tiny)
					return "فرو";
				else
					return "فروردین";
				break;
			case 2:
				if ($tiny)
					return "ارد";
				else
					return "اردیبهشت";
				break;
			case 3:
				if ($tiny)
					return "خرد";
				else
					return "خرداد";
				break;
			case 4:
				if ($tiny)
					return "تیر";
				else
					return "تير";
				break;
			case 5:
				if ($tiny)
					return "مرد";
				else
					return "مرداد";
				break;
			case 6:
				if ($tiny)
					return "شهر";
				else
					return "شهریور";
				break;
			case 7:
				return "مهر";
				break;
			case 8:
				if ($tiny)
					return "آبا";
				else
					return "آبان";
				break;
			case 9:
				return "آذر";
				break;
			case 10:
				return "دى";
				break;
			case 11:
				if ($tiny)
					return "بهم";
				else
					return "بهمن";
				break;
			case 12:
				if ($tiny)
					return "اصف";
				else
					return "اسفند";
				break;
		}
	}


	/**
	 * return day of year
	 *
	 * @param $month
	 * @param $day
	 *
	 * @return int
	 */
	private function day_of_year($month, $day)
	{
		return $month <= 6 ?
			(($month - 1) * 31 + $day) :
			186 + (($month - 6 - 1) * 30 + $day);
	}


	/**
	 * is the given year leap year or not
	 *
	 * @param $yearValue
	 *
	 * @return mixed
	 */
	private function is_leap_year($year)
	{
		return array_search((($year + 2346) % 2820) % 128, [
			5, 9, 13, 17, 21, 25, 29,
			34, 38, 42, 46, 50, 54, 58, 62,
			67, 71, 75, 79, 83, 87, 91, 95,
			100, 104, 108, 112, 116, 120, 124, 0,
		]);
	}

	/*

	/ **
	 * Create a Carbon instance from a specific format.
	 *
	 * @param string                    $format
	 * @param string                    $time
	 * @param \DateTimeZone|string|null $tz
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return static
	 * /
	public static function createFromFormat($format, $time, $tz = null)
	{
		if ($tz !== null) {
			$dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($tz));
		} else {
			$dt = parent::createFromFormat($format, $time);
		}

		if ($dt instanceof DateTime) {
			return static::instance($dt);
		}

		$errors = static::getLastErrors();
		throw new InvalidArgumentException(implode(PHP_EOL, $errors['errors']));
	}

	 */


	/**
	 * @param $format
	 * @param $date
	 *
	 * @return array
	 *
	 * @TODO
	 */
	public function parseFromFormat($format, $date)
	{
		// reverse engineer date formats
		$keys = [
			'Y' => ['year', '\d{4}'],
			'y' => ['year', '\d{2}'],
			'm' => ['month', '\d{2}'],
			'n' => ['month', '\d{1,2}'],
			'M' => ['month', '[A-Z][a-z]{3}'],
			'F' => ['month', '[A-Z][a-z]{2,8}'],
			'd' => ['day', '\d{2}'],
			'j' => ['day', '\d{1,2}'],
			'D' => ['day', '[A-Z][a-z]{2}'],
			'l' => ['day', '[A-Z][a-z]{6,9}'],
			'u' => ['hour', '\d{1,6}'],
			'h' => ['hour', '\d{2}'],
			'H' => ['hour', '\d{2}'],
			'g' => ['hour', '\d{1,2}'],
			'G' => ['hour', '\d{1,2}'],
			'i' => ['minute', '\d{2}'],
			's' => ['second', '\d{2}'],
		];
		// convert format string to regex
		$regex = '';
		$chars = str_split($format);
		foreach ($chars as $n => $char) {
			$lastChar = isset($chars[ $n - 1 ]) ? $chars[ $n - 1 ] : '';
			$skipCurrent = '\\' == $lastChar;
			if (!$skipCurrent && isset($keys[ $char ])) {
				$regex .= '(?P<' . $keys[ $char ][0] . '>' . $keys[ $char ][1] . ')';
			} else {
				if ('\\' == $char) {
					$regex .= $char;
				} else {
					$regex .= preg_quote($char);
				}
			}
		}
		$dt = [];
		$dt['error_count'] = 0;
		// now try to match it
		if (preg_match('#^' . $regex . '$#', $date, $dt)) {
			foreach ($dt as $k => $v) {
				if (is_int($k)) {
					unset($dt[ $k ]);
				}
			}
			if (!jDateTime::checkdate($dt['month'], $dt['day'], $dt['year'], false)) {
				$dt['error_count'] = 1;
			}
		} else {
			$dt['error_count'] = 1;
		}
		$dt['errors'] = [];
		$dt['fraction'] = '';
		$dt['warning_count'] = 0;
		$dt['warnings'] = [];
		$dt['is_localtime'] = 0;
		$dt['zone_type'] = 0;
		$dt['zone'] = 0;
		$dt['is_dst'] = '';
		if (strlen($dt['year']) == 2) {
			$now = self::forge('now');
			$x = $now->format('Y') - $now->format('y');
			$dt['year'] += $x;
		}
		$dt['year'] = isset($dt['year']) ? (int)$dt['year'] : 0;
		$dt['month'] = isset($dt['month']) ? (int)$dt['month'] : 0;
		$dt['day'] = isset($dt['day']) ? (int)$dt['day'] : 0;
		$dt['hour'] = isset($dt['hour']) ? (int)$dt['hour'] : 0;
		$dt['minute'] = isset($dt['minute']) ? (int)$dt['minute'] : 0;
		$dt['second'] = isset($dt['second']) ? (int)$dt['second'] : 0;
		return $dt;
	}
}