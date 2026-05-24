<?php
/**
 * Tiny declarative validator for the registry input DSL.
 *
 * Field rule grammar (pipe-separated):
 *   <type>[|<rule1>[|<rule2>...]]
 *
 *   type:   int | string | bool
 *   rule:   min:N | max:N | maxlen:N | default:V | required
 *
 * Returns sanitized assoc array — fields with `default:` filled in when missing.
 * Unknown fields are silently dropped (caller never sees them).
 */

declare( strict_types = 1 );

namespace EZ\Ajax;

final class InputValidator {

	/**
	 * @param array<string,string> $spec
	 * @param array<string,mixed>  $raw
	 *
	 * @return array{0:array<string,mixed>,1:?string} Validated inputs + error code on failure.
	 */
	public static function validate( array $spec, array $raw ): array {
		$out = [];
		foreach ( $spec as $name => $rule_str ) {
			$parts    = explode( '|', $rule_str );
			$type     = array_shift( $parts );
			$has_val  = array_key_exists( $name, $raw );
			$value    = $has_val ? $raw[ $name ] : null;
			$required = false;
			$default  = null;
			$min      = null;
			$max      = null;
			$maxlen   = null;

			foreach ( $parts as $part ) {
				if ( 'required' === $part ) {
					$required = true;
					continue;
				}
				if ( str_starts_with( $part, 'min:' ) ) {
					$min = (int) substr( $part, 4 );
					continue;
				}
				if ( str_starts_with( $part, 'max:' ) ) {
					$max = (int) substr( $part, 4 );
					continue;
				}
				if ( str_starts_with( $part, 'maxlen:' ) ) {
					$maxlen = (int) substr( $part, 7 );
					continue;
				}
				if ( str_starts_with( $part, 'default:' ) ) {
					$default = substr( $part, 8 );
					continue;
				}
			}

			if ( ! $has_val || null === $value || '' === $value ) {
				if ( $required ) {
					return [ [], 'BAD_REQUEST' ];
				}
				if ( null !== $default ) {
					$value = $default;
				} else {
					continue;
				}
			}

			switch ( $type ) {
				case 'int':
					if ( ! is_numeric( $value ) ) {
						return [ [], 'BAD_REQUEST' ];
					}
					$value = (int) $value;
					if ( null !== $min && $value < $min ) {
						return [ [], 'BAD_REQUEST' ];
					}
					if ( null !== $max && $value > $max ) {
						return [ [], 'BAD_REQUEST' ];
					}
					break;
				case 'bool':
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					if ( null === $value ) {
						return [ [], 'BAD_REQUEST' ];
					}
					break;
				case 'string':
				default:
					if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
						return [ [], 'BAD_REQUEST' ];
					}
					$value = (string) $value;
					if ( null !== $maxlen && strlen( $value ) > $maxlen ) {
						return [ [], 'BAD_REQUEST' ];
					}
					break;
			}

			$out[ $name ] = $value;
		}

		return [ $out, null ];
	}
}
