<?php namespace App\Larabookir;

/**
 * Holder for SEO Rank information
 */
class WPSEO_Rank {

	const BAD      = 'bad';
	const OK       = 'ok';
	const GOOD     = 'good';
	const NO_FOCUS = 'na';
	const NO_INDEX = 'noindex';

	/**
	 * @var array All possible ranks.
	 */
	protected static $ranks = array(
		self::BAD,
		self::OK,
		self::GOOD,
		self::NO_FOCUS,
		self::NO_INDEX,
	);

	/**
	 * Holds the translation from seo score slug to actual score range
	 *
	 * @var array
	 */
	protected static $ranges = array(
		self::NO_FOCUS => array(
			'start' => 0,
			'end'   => 0,
		),
		self::BAD  => array(
			'start' => 1,
			'end'   => 40,
		),
		self::OK => array(
			'start' => 41,
			'end'   => 70,
		),
		self::GOOD => array(
			'start' => 71,
			'end'   => 100,
		),
	);

	/**
	 * @var int
	 */
	protected $rank;

	/**
	 * @param int $rank The actual rank.
	 */
	public function __construct( $rank ) {
		if ( ! in_array( $rank, self::$ranks ) ) {
			$rank = self::BAD;
		}

		$this->rank = $rank;
	}

	/**
	 * Returns the saved rank for this rank.
	 *
	 * @return string
	 */
	public function get_rank() {
		return $this->rank;
	}

	/**
	 * Returns a CSS class for this rank
	 *
	 * @return string
	 */
	public function get_css_class() {
		$labels = array(
			self::NO_FOCUS => 'na',
			self::NO_INDEX => 'noindex',
			self::BAD      => 'bad',
			self::OK       => 'ok',
			self::GOOD     => 'good',
		);

		return $labels[ $this->rank ];
	}

	/**
	 * Returns a label for this rank
	 *
	 * @return string
	 */
	public function get_label() {
		$labels = array(
			self::NO_FOCUS => 'موجود نیست',
			self::NO_INDEX => 'غیر  قابل ایندکس',
			self::BAD      => 'بد',
			self::OK       => 'نسبتا خوب',
			self::GOOD     => 'خوب',
		);

		return $labels[ $this->rank ];
	}

	/**
	 * Returns a label for use in a drop down
	 *
	 * @return mixed
	 */
	public function get_drop_down_label() {
		$labels = array(
			self::NO_FOCUS => 'SEO: No Focus Keyword',
			self::BAD      => 'SEO: Bad',
			self::OK       => 'SEO: OK',
			self::GOOD     => 'SEO: Good',
			self::NO_INDEX => 'SEO: Post Noindexed',
		);

		return $labels[ $this->rank ];
	}

	/**
	 * @return int The starting score for this rank.
	 */
	public function get_starting_score() {
		// No index does not have a starting score.
		if ( self::NO_INDEX === $this->rank ) {
			return -1;
		}

		return self::$ranges[ $this->rank ]['start'];
	}

	/**
	 * @return int The end score for this rank.
	 */
	public function get_end_score() {
		// No index does not have an end score.
		if ( self::NO_INDEX === $this->rank ) {
			return -1;
		}

		return self::$ranges[ $this->rank ]['end'];
	}

	/**
	 * Returns a rank for a specific numeric score
	 *
	 * @param int $score The score to determine a rank for.
	 *
	 * @return self
	 */
	public static function from_numeric_score( $score ) {
		// Set up the default value.
		$rank = new self( self::BAD );

		foreach ( self::$ranges as $rank_index => $range ) {
			if ( $range['start'] <= $score && $score <= $range['end'] ) {
				$rank = new self( $rank_index );
				break;
			}
		}

		return $rank;
	}

	/**
	 * Returns a list of all possible SEO Ranks
	 *
	 * @return WPSEO_Rank[]
	 */
	public static function get_all_ranks() {
		return array_map( array( 'WPSEO_Rank', 'create_rank' ), self::$ranks );
	}

	/**
	 * Converts a numeric rank into a WPSEO_Rank object, for use in functional array_* functions
	 *
	 * @param string $rank SEO Rank.
	 *
	 * @return WPSEO_Rank
	 */
	private static function create_rank( $rank ) {
		return new self( $rank );
	}
}
