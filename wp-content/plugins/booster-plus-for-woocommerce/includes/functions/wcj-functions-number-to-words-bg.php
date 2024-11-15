<?php
/**
 * Booster for WooCommerce - Functions - Numbers to Words - BG
 *
 * @version 6.0.0
 * @author  Pluggabl LLC.
 * @package Booster_Plus_For_WooCommerce/functions
 */

if ( ! function_exists( 'convert_number_to_words_bg' ) ) {
	/**
	 * Convert_number_to_words_bg.
	 *
	 * @version 6.0.0
	 * @return  string
	 * @param int   $num Get numbers.
	 * @param array $tri Words.
	 */
	function convert_number_to_words_bg( $num, $tri = 0 ) {
		$edinici = array(
			0  => '',
			1  => array(
				0  => ' един',
				1  => '',
				2  => ' eдин',
				3  => ' eдин',
				4  => ' eдин',
				5  => ' eдин',
				6  => ' eдин',
				7  => ' eдин',
				8  => ' eдин',
				9  => ' eдин',
				10 => ' eдин',
			),
			2  => array(
				0  => ' два',
				1  => ' двe',
				2  => ' два',
				3  => ' два',
				4  => ' два',
				5  => ' два',
				6  => ' два',
				7  => ' два',
				8  => ' два',
				9  => ' два',
				10 => ' два',
			),
			3  => ' три',
			4  => ' четири',
			5  => ' пет',
			6  => ' шест',
			7  => ' седем',
			8  => ' осем',
			9  => ' девет',
			10 => ' десет',
			11 => ' единадесет',
			12 => ' дванадесет',
			13 => ' тринадесет',
			14 => ' четиринадесет',
			15 => ' петнадесет',
			16 => ' шестнадесет',
			17 => ' седемнадесет',
			18 => ' осемнадесет',
			19 => ' деветнадесет',
		);

		$desetici = array(
			0 => '',
			1 => '',
			2 => ' двадесет',
			3 => ' тридесет',
			4 => ' четиридесет',
			5 => ' петдесет',
			6 => ' шестдесет',
			7 => ' седемдесет',
			8 => ' осемдесет',
			9 => ' деведесет',
		);

		$stotici = array(
			0 => '',
			1 => ' сто',
			2 => ' двеста',
			3 => ' триста',
			4 => ' четиристотин',
			5 => ' петстотин',
			6 => ' шестстотин',
			7 => ' седемстотин',
			8 => ' осемстотин',
			9 => ' деветстотин',
		);

		$tripleti = array(
			0  => '',
			1  => array(
				0 => ' хиляда',
				1 => ' хиляди',
			),
			2  => array(
				0 => ' милион',
				1 => ' милиона',
			),
			3  => array(
				0 => ' билион',
				1 => ' билионa',
			),
			4  => array(
				0 => ' трилион',
				1 => ' трилиона',
			),
			5  => array(
				0 => ' квадрилион',
				1 => ' квадрилиона',
			),
			6  => array(
				0 => ' квинтилион',
				1 => ' квинтилиони',
			),
			7  => array(
				0 => ' сикстилион',
				1 => ' сикстилион',
			),
			8  => array(
				0 => ' септилион',
				1 => ' септилиони',
			),
			9  => array(
				0 => ' октилион',
				1 => ' октилион',
			),
			10 => array(
				0 => ' нонилион',
				1 => ' нонилиои',
			),
		);

		// взимаме само цялата част от числото, без стойността.
		// след десетичната запетая.
		$n   = explode( '.', $num );
		$num = $n[0];
		$r   = (int) ( $num / 1000 );
		$x   = ( $num / 100 ) % 10;
		$y   = $num % 100;

		$str = '';

		// стотици.
		if ( $x > 0 ) {
			$str = $stotici[ $x ];
		}
		// единици и десетици.
		if ( $y < 20 ) {
			if ( 0 === $y && $r > 0 ) {
				$str = ' и ' . $str;
			}
			if ( is_array( $edinici[ $y ] ) && isset( $edinici[ $y ][ $tri ] ) ) {
				$str .= ' ' . $edinici[ $y ][ $tri ];
			} else {
				$str .= ' ' . $edinici[ $y ];
			}
		} else {
			if ( $edinici[ $y % 10 ] ) {
				$str .= $desetici[ (int) ( $y / 10 ) ];
				$str .= ' и';
				if ( is_array( $edinici[ $y % 10 ] ) && isset( $edinici[ $y % 10 ][ $tri ] ) ) {
					$str .= $edinici[ $y % 10 ][ $tri ];
				} else {
					$str .= $edinici[ $y % 10 ];
				}
			} else {
				$str .= ' и' . $desetici[ (int) ( $y / 10 ) ];
			}
		}

		// добавяне на модификатор - хиляди, милиони, билиони.

		if ( '' !== $str ) {
			// Ако има зададени опции за единствено и мн. число.
			if ( is_array( $tripleti[ $tri ] ) ) {
				// мн. число ли е?.
				if ( $num > 1 ) {
					$str .= $tripleti[ $tri ][1];
				} else {
					$str .= $tripleti[ $tri ][0];
				}
			} else {
				$str .= $tripleti[ $tri ];
			}
			$str = str_replace( 'един стотин', 'сто', $str );
			$str = str_replace( 'един хиляди', 'хиляда', $str );
		}

		if ( $r > 0 ) {
			return convert_number_to_words_bg( $r, $tri + 1 ) . $str;
		} else {
			return $str;
		}
	}
}
