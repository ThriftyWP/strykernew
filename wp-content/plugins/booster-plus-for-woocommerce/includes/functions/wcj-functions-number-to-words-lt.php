<?php
/**
 * Booster for WooCommerce - Functions - Numbers to Words
 *
 * @version 6.0.0
 * @since  1.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/functions
 */

if ( ! function_exists( 'convert_number_to_words_lt' ) ) {
	/**
	 * Convert_number_to_words_lt.
	 *
	 * @version 6.0.0
	 * @since  1.0.0
	 * @return  string
	 * @param int $number Get Number.
	 */
	function convert_number_to_words_lt( $number ) {
		$hyphen      = ' ';
		$conjunction = '   ';
		$separator   = ', ';
		$negative    = 'minus ';
		$decimal     = ' . ';
		$dictionary  = array(
			0                    => 'nulis',
			1                    => 'vienas',
			2                    => 'du',
			3                    => 'trys',
			4                    => 'keturi',
			5                    => 'penki',
			6                    => 'šeši',
			7                    => 'septyni',
			8                    => 'aštuoni',
			9                    => 'devyni',
			10                   => 'dešimt',
			11                   => 'vienuolika',
			12                   => 'dvylika',
			13                   => 'trylika',
			14                   => 'keturiolika',
			15                   => 'penkiolika',
			16                   => 'šešiolika',
			17                   => 'septyniolika',
			18                   => 'aštoniolika',
			19                   => 'devyniolika',
			20                   => 'dvidešimt',
			30                   => 'trisdešimt',
			40                   => 'keturiasdešimt',
			50                   => 'penkiasdešimt',
			60                   => 'šešiasdešimt',
			70                   => 'septyniasdešimt',
			80                   => 'aštuoniasdešimt',
			90                   => 'devyniasdešimt',
			100                  => 'šimtas',
			200                  => 'šimtai',
			1000                 => 'tūkstantis',
			2000                 => 'tūkstančiai',
			10000                => 'tūkstančių',
			1000000              => 'milijonas',
			2000000              => 'milijonai',
			10000000             => 'milijonų',
			1000000000           => 'bilijonas',
			2000000000           => 'bilijonai',
			10000000000          => 'bilijonų',
			1000000000000        => 'trilijonas',
			2000000000000        => 'trilijonai',
			10000000000000       => 'trilijonų',
			1000000000000000     => 'kvadrilijonas',
			2000000000000000     => 'kvadrilijonai',
			10000000000000000    => 'kvadrilijonų',
			1000000000000000000  => 'kvintilijonas',
			2000000000000000000  => 'kvintilijonai',
			10000000000000000000 => 'kvintilijonų',
		);

		if ( ! is_numeric( $number ) ) {
			return false;
		}

		if ( ( $number >= 0 && (int) $number < 0 ) || (int) $number < 0 - PHP_INT_MAX ) {
			// overflow.
			trigger_error( // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				'convert_number_to_words_lt only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				E_USER_WARNING
			);
			return false;
		}

		if ( $number < 0 ) {
			return $negative . convert_number_to_words_lt( abs( $number ) );
		}
		$fraction = null;
		$string   = $fraction;

		if ( strpos( $number, '.' ) !== false ) {
			list($number, $fraction) = explode( '.', $number );
		}

		switch ( true ) {
			case $number < 21:
				$string = $dictionary[ $number ];
				break;
			case $number < 100:
				$tens   = ( (int) ( $number / 10 ) ) * 10;
				$units  = $number % 10;
				$string = $dictionary[ $tens ];
				if ( $units ) {
					$string .= $hyphen . $dictionary[ $units ];
				}
				break;
			case $number < 200:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string    = $dictionary[ $hundreds ] . ' ' . $dictionary[100];
				if ( $remainder ) {
					$string .= $conjunction . convert_number_to_words_lt( $remainder );
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string    = $dictionary[ $hundreds ] . ' ' . $dictionary[200];
				if ( $remainder ) {
					$string .= $conjunction . convert_number_to_words_lt( $remainder );
				}
				break;

			default:
				$base_unit      = pow( 1000, floor( log( $number, 1000 ) ) );
				$num_base_units = (int) ( $number / $base_unit );
				$number1        = (string) $number;
				if ( 1 === $num_base_units ) {
					$base_units = $base_unit;
				} elseif ( $num_base_units < 10 ) {
					$base_units = $base_unit * 2;
				} else {
					$base_units = $base_unit * 10;}

				$remainder = $number % $base_unit;
				$string    = convert_number_to_words_lt( $num_base_units ) . ' ' . $dictionary[ $base_units ];
				if ( $remainder ) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= convert_number_to_words_lt( $remainder );
				}
				break;
		}

		if ( null !== $fraction && is_numeric( $fraction ) ) {
			$string .= $decimal;
			$words   = array();
			foreach ( str_split( (string) $fraction ) as $number ) {
				$words[] = $dictionary[ $number ];
			}
			$string .= implode( ' ', $words );
		}

		return $string;
	}
}
