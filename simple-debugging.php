<?php
/**
 * Simple Debugging plugin for WordPress
 * @package   simple-debugging
 * @link      https://github.com/juanjopuntcat/simple-debugging
 * @author    Juanjo Rubio
 * @copyright 2025 Juanjo Rubio
 * @license   GPL v2 or later
 *
 * Plugin Name:  Simple Debugging
 * Description:  Easily enable and manage WordPress debugging with a safe, user-friendly admin UI. Toggle debug settings, control access by user role, and review logs — no need to edit wp-config.php manually.
 * Version:      1.0.0
 * Plugin URI:   https://github.com/juanjopuntcat/simple-debugging
 * Author:       Juanjo Rubio
 * Author URI:   https://github.com/juanjopuntcat
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:  simple-debugging
 * Domain Path:  /languages/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SD_PATH', plugin_dir_path( __FILE__ ) );

// === INCLUDE TABS ===
require_once SD_PATH . 'inc/class-sd-tab-general.php';
require_once SD_PATH . 'inc/class-sd-tab-roles.php';
require_once SD_PATH . 'inc/class-sd-tab-log.php';

// === HELPER: Get default options ===
function sd_get_default_options() {
    $roles = [];
    if ( function_exists( 'get_editable_roles' ) ) {
        foreach ( get_editable_roles() as $role => $data ) {
            $roles[ $role ] = ( $role === 'administrator' ) ? 'write' : 'none';
        }
    }
    return [
        'debug'       => false,
        'log'         => false,
        'display'     => false,
        'role_access' => $roles,
    ];
}

// === ADMIN MENU ===
add_action( 'admin_menu', function() {
    $user = wp_get_current_user();
    $opts = get_option( 'sd_options', sd_get_default_options() );
    if ( empty( $opts['role_access'] ) ) {
        $opts['role_access'] = sd_get_default_options()['role_access'];
    }
    $can_access = false;
    foreach ( (array) $user->roles as $role ) {
        if ( $role === 'administrator' || ( isset( $opts['role_access'][ $role ] ) && in_array( $opts['role_access'][ $role ], [ 'read', 'write' ], true ) ) ) {
            $can_access = true;
            break;
        }
    }
    if ( $can_access ) {
        add_submenu_page(
            'tools.php',
            esc_html__( 'Simple Debugging', 'simple-debugging' ),
            esc_html__( 'Simple Debugging', 'simple-debugging' ),
            'read',
            'simple-debugging',
            'sd_render_settings_page'
        );
    }
});

// === ENQUEUE ASSETS (JS/CSS) ON PLUGIN PAGE ONLY ===
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'simple-debugging' ) {
        wp_enqueue_style(
            'sd-admin-css',
            plugins_url( 'assets/admin.css', __FILE__ ),
            [],
            filemtime( SD_PATH . 'assets/admin.css' )
        );
        wp_enqueue_script(
            'sd-admin-js',
            plugins_url( 'assets/admin.js', __FILE__ ),
            [ 'jquery' ],
            filemtime( SD_PATH . 'assets/admin.js' ),
            true
        );
        wp_localize_script( 'sd-admin-js', 'SD_DEBUG', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'sd_load_log' ),
            'i18n'    => [
                'loading' => esc_html__( 'Loading log entries...', 'simple-debugging' ),
                'none'    => esc_html__( 'No log entries found.', 'simple-debugging' ),
                'page'    => esc_html__( 'Page', 'simple-debugging' ),
                'of'      => esc_html__( 'of', 'simple-debugging' ),
                'prev'    => esc_html__( 'Prev', 'simple-debugging' ),
                'next'    => esc_html__( 'Next', 'simple-debugging' ),
            ]
        ] );
    }
});

// === SETTINGS PAGE RENDER ===
function sd_render_settings_page() {
    $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
    $tabs = [
        'general' => esc_html__( 'General', 'simple-debugging' ),
        'roles'   => esc_html__( 'Roles',   'simple-debugging' ),
        'log'     => esc_html__( 'Log',     'simple-debugging' )
    ];
    echo '<div class="wrap"><h1>' . esc_html__( 'Simple Debugging', 'simple-debugging' ) . '</h1>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $tabs as $key => $label ) {
        printf(
            '<a href="%s" class="nav-tab%s">%s</a>',
            esc_url( admin_url( 'tools.php?page=simple-debugging&tab=' . $key ) ),
            $tab === $key ? ' nav-tab-active' : '',
            esc_html( $label )
        );
    }
    echo '</h2>';
    echo '<form method="post" action="">';
    wp_nonce_field( 'sd_update_settings', 'sd_nonce' );

    // Show admin notice if needed
    do_action( 'sd_admin_notices' );

    // Tab content
    switch ( $tab ) {
        case 'roles':
            ( new SD_Tab_Roles() )->render();
            break;
        case 'log':
            ( new SD_Tab_Log() )->render();
            break;
        case 'general':
        default:
            ( new SD_Tab_General() )->render();
            break;
    }
    echo '</form></div>';
}

// === SAVE FORM SUBMISSION (FOR GENERAL AND ROLES TABS) ===
add_action( 'admin_init', function() {
    if (
        isset( $_POST['sd_nonce'] )
        && wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['sd_nonce'] ) ),
            'sd_update_settings'
        )
    ) {
        $tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
        $opts = get_option( 'sd_options', sd_get_default_options() );
        if ( $tab === 'general' ) {
            $debug   = ! empty( sanitize_text_field( wp_unslash( $_POST['sd_debug'] ?? '' ) ) );
            $log     = $debug ? ! empty( sanitize_text_field( wp_unslash( $_POST['sd_log'] ?? '' ) ) ) : false;
            $display = $debug ? ! empty( sanitize_text_field( wp_unslash( $_POST['sd_display'] ?? '' ) ) ) : false;
            $opts['debug']   = $debug;
            $opts['log']     = $log;
            $opts['display'] = $display;
            update_option( 'sd_options', $opts );
            sd_update_wpconfig_debug_settings( $debug, $log, $display );
        } elseif ( $tab === 'roles' ) {
            if (
                isset( $_POST['sd_role_access'] )
                && is_array( $_POST['sd_role_access'] )
                && function_exists( 'get_editable_roles' )
            ) {
                $role_access = [];
                if ( isset( $_POST['sd_role_access'] ) && is_array( $_POST['sd_role_access'] ) ) {
                    foreach ( wp_unslash( $_POST['sd_role_access'] ) as $role => $value ) {
                        $role_access[ sanitize_key( $role ) ] = sanitize_text_field( $value );
                    }
                }
                foreach ( get_editable_roles() as $role => $data ) {
                    if ( $role === 'administrator' ) continue;
                    $val = isset( $role_access[ $role ] ) ? $role_access[ $role ] : 'none';
                    $opts['role_access'][ $role ] = in_array( $val, [ 'none', 'read', 'write' ], true ) ? $val : 'none';
                }
                update_option( 'sd_options', $opts );
            }
        }
        $tabq = $tab ? '&tab=' . urlencode( $tab ) : '';
        wp_redirect( admin_url( 'tools.php?page=simple-debugging' . $tabq . '&settings-updated=1' ) );
        exit;
    }
});

// === ADMIN NOTICE IF MANUAL EDIT REQUIRED ===
add_action( 'sd_admin_notices', function() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $opts = get_option( 'sd_options', sd_get_default_options() );
    $wpconfig = sd_find_wpconfig();
    if ( ! $wpconfig || ! sd_is_wpconfig_writable( $wpconfig ) ) {
        echo '<div class="notice notice-error"><p><strong>Simple Debugging: Unable to write to <code>wp-config.php</code>.</strong><br />'
            . wp_kses_post( __( 'Please add the following block <b>above</b> the <code>require_once</code> line in your <code>wp-config.php</code>:', 'simple-debugging' ) )
            . '</p><pre style="user-select:all;">'
            . esc_html(
                "// BEGIN SD DEBUG (Simple Debugging plugin)\n"
                . "define('WP_DEBUG', " . ( $opts['debug'] ? 'true' : 'false' ) . ");\n"
                . "define('WP_DEBUG_LOG', " . ( $opts['log'] ? 'true' : 'false' ) . ");\n"
                . "define('WP_DEBUG_DISPLAY', " . ( $opts['display'] ? 'true' : 'false' ) . ");\n"
                . "// END SD DEBUG"
            )
            . '</pre></div>';
    }
});

// === FIND WP-CONFIG.PHP (ONE LEVEL UP OR IN ROOT) ===
function sd_find_wpconfig() {
    $abspath = ABSPATH;
    $locations = [
        $abspath . 'wp-config.php',
        dirname( $abspath ) . '/wp-config.php'
    ];
    foreach ( $locations as $file ) {
        if ( file_exists( $file ) ) return $file;
    }
    return false;
}

// === WP Filesystem check for writable wp-config.php ===
function sd_is_wpconfig_writable( $file ) {
    global $wp_filesystem;
    if ( ! function_exists( 'request_filesystem_credentials' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if ( empty( $wp_filesystem ) ) {
        $creds = request_filesystem_credentials( '', '', false, false, null );
        if ( ! WP_Filesystem( $creds ) ) {
            return false;
        }
    }
    return $wp_filesystem->is_writable( $file );
}

// === UPDATE/WRITE DEBUG SETTINGS IN WP-CONFIG.PHP USING WP_FILESYSTEM ===
function sd_update_wpconfig_debug_settings( $debug, $log, $display ) {
    $wpconfig = sd_find_wpconfig();
    if ( ! $wpconfig ) return false;
    global $wp_filesystem;
    if ( ! function_exists( 'request_filesystem_credentials' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if ( empty( $wp_filesystem ) ) {
        $creds = request_filesystem_credentials( '', '', false, false, null );
        if ( ! WP_Filesystem( $creds ) ) {
            return false;
        }
    }
    if ( ! $wp_filesystem->is_writable( $wpconfig ) ) return false;
    $contents = $wp_filesystem->get_contents( $wpconfig );
    $pattern = '/\/\/ BEGIN SD DEBUG \(Simple Debugging plugin\).*?\/\/ END SD DEBUG\s*/s';
    $block = "// BEGIN SD DEBUG (Simple Debugging plugin)\n"
        . "define('WP_DEBUG', " . ( $debug ? 'true' : 'false' ) . ");\n"
        . "define('WP_DEBUG_LOG', " . ( $log ? 'true' : 'false' ) . ");\n"
        . "define('WP_DEBUG_DISPLAY', " . ( $display ? 'true' : 'false' ) . ");\n"
        . "// END SD DEBUG\n";
    if ( preg_match( $pattern, $contents ) ) {
        $contents = preg_replace( $pattern, $block, $contents );
    } else {
        // Insert above require_once
        if ( strpos( $contents, 'require_once' ) !== false ) {
            $contents = preg_replace( '/(\s*require_once\s*\(?[\'"]{1}\$abspath.+)/i', $block . '$1', $contents, 1 );
        } else {
            $contents .= "\n" . $block;
        }
    }
    $wp_filesystem->put_contents( $wpconfig, $contents, FS_CHMOD_FILE );
    return true;
}

// === AJAX LOG LOADER (PHP side, outputting JSON for JS table) ===
add_action( 'wp_ajax_sd_load_log', function() {
    check_ajax_referer( 'sd_load_log' );
    $file = WP_CONTENT_DIR . '/debug.log';
    if ( ! is_readable( $file ) ) wp_send_json( [ 'rows' => [], 'total' => 0 ] );
    $lines = @file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    $total = count( $lines );
    $paged = max( 1, intval( $_POST['paged'] ?? 1 ) );
    $per_page = min( 100, max( 1, intval( $_POST['per_page'] ?? 25 ) ) );
    $slice = array_slice( $lines, -($paged * $per_page), $per_page );
    $slice = array_reverse( $slice );
    $rows = [];
    foreach ( $slice as $line ) {
        $timestamp = $title = $desc = $filecol = $linecol = '';
        if ( preg_match( '/^\[([^\]]+)\]\s*([A-Za-z ]+):\s*(.+)$/', $line, $m ) ) {
            $timestamp = trim( $m[1] );
            $title    = trim( $m[2] );
            $desc     = trim( $m[3] );
        } else {
            $desc = trim( $line );
        }
        // Extract file and line number from message
        if ( preg_match( '/ in ([^ ]+) on line (\d+)/', $desc, $mm ) ) {
            $filecol = $mm[1];
            $linecol = $mm[2];
        } elseif ( preg_match( '/([^\s:]+\.php)\((\d+)\)/', $desc, $mm ) ) {
            $filecol = $mm[1];
            $linecol = $mm[2];
        }
        $desc_html = wp_kses_post( $desc );
        if ( strlen( wp_strip_all_tags( $desc_html ) ) > 250 ) {
            $desc_html = '<div style="max-height:180px;overflow:auto">' . $desc_html . '</div>';
        }
        $rows[] = [
            'timestamp'   => esc_html( $timestamp ),
            'title'       => esc_html( $title ),
            'file'        => esc_html( $filecol ),
            'line'        => esc_html( $linecol ),
            'description' => $desc_html
        ];
    }
    wp_send_json( [ 'rows' => $rows, 'total' => $total ] );
} );
