<?php
/**
 * Roles Tab for Simple Debugging
 *
 * @package   simple-debugging
 */

// Ensure sd_get_defaults is available (optional: include from a helpers file)
if ( ! function_exists( 'sd_get_defaults' ) ) {
    function sd_get_defaults() {
        // You can replace this with a require_once if you have a helpers file
        $defaults = array(
            'wp_debug'        => 1,
            'wp_debug_log'    => 1,
            'wp_debug_display'=> 1,
            'role_access'     => array(),
        );
        if ( function_exists( 'get_editable_roles' ) ) {
            foreach ( get_editable_roles() as $role => $data ) {
                $defaults['role_access'][ $role ] = ( 'administrator' === $role ) ? 'write' : 'none';
            }
        }
        return $defaults;
    }
}

class SD_Tab_Roles {
    public function render() {
        $opts  = wp_parse_args( get_option( 'sd_options', array() ), sd_get_defaults() );
        $roles = function_exists( 'get_editable_roles' ) ? get_editable_roles() : array();

        echo '<table class="widefat" style="width:100%;border-collapse:collapse;">';
        echo '<thead><tr>';
        echo '<th style="padding:12px;text-align:left;">' . esc_html__( 'Role', 'simple-debugging' ) . '</th>';
        echo '<th style="padding:12px;text-align:left;">' . esc_html__( 'Access', 'simple-debugging' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $roles as $role => $data ) {
            $sel = isset( $opts['role_access'][ $role ] ) ? $opts['role_access'][ $role ] : 'none';
            $dis = ( 'administrator' === $role );
            echo '<tr>';
            echo '<td style="padding:12px;">' . esc_html( $data['name'] ) . '</td>';
            echo '<td style="padding:12px;">';
            echo '<select name="sd_options[role_access][' . esc_attr( $role ) . ']"';
            if ( $dis ) {
                echo ' disabled';
            }
            echo ' style="width:100%;">';
            foreach (
                array(
                    'none'  => esc_html__( 'None', 'simple-debugging' ),
                    'read'  => esc_html__( 'Read', 'simple-debugging' ),
                    'write' => esc_html__( 'Write', 'simple-debugging' ),
                ) as $lvl => $lbl
            ) {
                printf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    esc_attr( $lvl ),
                    selected( $sel, $lvl, false ),
                    esc_html( $lbl )
                );
            }
            echo '</select>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}
