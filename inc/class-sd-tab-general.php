<?php
// inc/class-sd-tab-general.php

if ( ! class_exists( 'SD_Tab_General' ) ) :

class SD_Tab_General {

    /**
     * Render the General settings tab.
     */
    public function render() {
        $opts = $this->get_options();
        $access = $this->user_access();

        ?>
        <p><?php esc_html_e('Configure main debug settings below. Changing these options writes directly to <code>wp-config.php</code>.', 'simple-debugging'); ?></p>
        <table class="form-table">
            <tr>
                <th><label for="sd_debug"><?php esc_html_e('Enable WP_DEBUG', 'simple-debugging'); ?></label></th>
                <td>
                    <input type="checkbox" id="sd_debug" name="sd_debug" value="1" <?php checked($opts['debug']); ?> <?php if ($access !== 'write') echo 'disabled'; ?>/>
                    <p class="description"><?php esc_html_e('Turns on WordPress internal debugging.', 'simple-debugging'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="sd_log"><?php esc_html_e('Enable WP_DEBUG_LOG', 'simple-debugging'); ?></label></th>
                <td>
                    <input type="checkbox" id="sd_log" name="sd_log" value="1" <?php checked($opts['log']); ?> <?php if (!$opts['debug'] || $access !== 'write') echo 'disabled'; ?>/>
                    <p class="description"><?php esc_html_e('Saves errors to wp-content/debug.log.', 'simple-debugging'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="sd_display"><?php esc_html_e('Enable WP_DEBUG_DISPLAY', 'simple-debugging'); ?></label></th>
                <td>
                    <input type="checkbox" id="sd_display" name="sd_display" value="1" <?php checked($opts['display']); ?> <?php if (!$opts['debug'] || $access !== 'write') echo 'disabled'; ?>/>
                    <p class="description"><?php esc_html_e('Shows errors on-site (not recommended for live sites as it may expose errors to frontend).', 'simple-debugging'); ?></p>
                </td>
            </tr>
        </table>
        <?php if ($access === 'write') submit_button(); ?>
        <?php
    }

    /**
     * Get options for the plugin.
     */
    private function get_options() {
        $defaults = [
            'debug'   => false,
            'log'     => false,
            'display' => false,
            'role_access' => [],
        ];
        $opts = get_option('sd_options', []);
        foreach ($defaults['role_access'] as $role => $def)
            if (!isset($opts['role_access'][$role]))
                $opts['role_access'][$role] = $def;
        return wp_parse_args($opts, $defaults);
    }

    /**
     * Check current user's access level (write/read/none).
     */
    private function user_access() {
        $opts = $this->get_options();
        if ( current_user_can( 'administrator' ) ) return 'write';
        $rw = false; $ro = false;
        $user = wp_get_current_user();
        foreach ( (array) $user->roles as $role ) {
            if (isset($opts['role_access'][$role])) {
                if ($opts['role_access'][$role] === 'write') $rw = true;
                if ($opts['role_access'][$role] === 'read')  $ro = true;
            }
        }
        return $rw ? 'write' : ($ro ? 'read' : 'none');
    }
}

endif;
