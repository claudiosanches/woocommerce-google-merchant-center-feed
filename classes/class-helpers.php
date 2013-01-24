<?php

class WC_Ultimate_Google_Product_Feed_Helpers {

    /**
     * Fix Category format.
     *
     * @param  string $category Category.
     *
     * @return string           Fixed category.
     */
    public function fix_category( $category ) {
        return str_replace( '>', '&gt;', $category );
    }

    /**
     * Fix Condition label.
     *
     * @param  int    $condition Condition option.
     *
     * @return string            Fixed condition.
     */
    public function fix_condition( $condition ) {
        switch ( $condition ) {
            case 1:
                $value = 'used';
                break;
            case 2:
                $value = 'refurbished';
                break;

            default:
                $value = 'new';
                break;
        }

        return $value;
    }

    /**
     * Fix Availability label.
     *
     * @param  int    $availability Availability option.
     *
     * @return string               Fixed availability.
     */
    public function fix_availability( $availability ) {
        switch ( $availability ) {
            case 1:
                $value = 'available for order';
                break;
            case 2:
                $value = 'out of stock';
                break;
            case 3:
                $value = 'preorder';
                break;

            default:
                $value = 'in stock';
                break;
        }

        return $value;
    }

    /**
     * Fix Gender label.
     *
     * @param  int    $gender Gender option.
     *
     * @return string         Fixed gender.
     */
    public function fix_gender( $gender ) {
        switch ( $gender ) {
            case 1:
                $value = 'female';
                break;
            case 2:
                $value = 'unisex';
                break;

            default:
                $value = 'male';
                break;
        }

        return $value;
    }

    /**
     * Fix Age Group label.
     *
     * @param  int    $age_group Age Group option.
     *
     * @return string            Fixed age group.
     */
    public function fix_age_group( $age_group ) {
        return ( 0 == $age_group ) ? 'adult' : 'kids';
    }

    /**
     * Fix tax.
     *
     * @param  string $values Tax in string format.
     *
     * @return array          Tax in array format.
     */
    public function fix_tax( $values ) {
        $tax = array();

        $values = explode( ',', $values );

        foreach ( $values as $value ) {
            $tax[] = explode( ':', $value );
        }

        return $tax;
    }

    /**
     * Fix date.
     *
     * @param  string $from From date.
     * @param  string $to   To date.
     *
     * @return string       Fixed date.
     */
    public function fix_date( $from, $to ) {
        return date( 'Y-m-d', $from ) . 'T00:00-0000/' . date( 'Y-m-d', $to ) . 'T24:00-0000';
    }

    public function fix_text( $string ) {
        // Fix new lines.
        $string = preg_replace('/\s\s+/', ' ', $string );

        return esc_attr( $string );
    }
}
