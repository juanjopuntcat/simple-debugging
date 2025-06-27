<?php
// inc/class-sd-tab-log.php

if ( ! class_exists( 'SD_Tab_Log' ) ) :

class SD_Tab_Log {

    /**
     * Render the Log tab UI
     */
    public function render() {
        ?>
        <p><?php esc_html_e('View, filter, and sort your <code>debug.log</code> entries below. You can paginate, search, and order by any column. Errors are shown as they appear in your log file.', 'simple-debugging'); ?></p>
        <div id="sd-log-controls" class="sd-list-table-filter">
            <label for="sd-log-size" style="font-weight:600;"><?php esc_html_e('Show', 'simple-debugging'); ?></label>
            <select id="sd-log-size">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span style="margin-left:16px;">
                <input type="search" id="sd-log-filter" placeholder="<?php esc_attresc_html_e('Filter log entries…','simple-debugging'); ?>" />
            </span>
        </div>
        <table id="sd-log-table" class="wp-list-table widefat fixed striped" style="width:100%;">
            <thead>
                <tr>
                    <th data-col="timestamp"><?php esc_html_e('Timestamp','simple-debugging'); ?> <span class="sd-sort-icon"></span></th>
                    <th data-col="title"><?php esc_html_e('Title','simple-debugging'); ?> <span class="sd-sort-icon"></span></th>
                    <th data-col="file"><?php esc_html_e('File','simple-debugging'); ?> <span class="sd-sort-icon"></span></th>
                    <th data-col="line"><?php esc_html_e('Line','simple-debugging'); ?> <span class="sd-sort-icon"></span></th>
                    <th data-col="description"><?php esc_html_e('Description','simple-debugging'); ?> <span class="sd-sort-icon"></span></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="5"><?php esc_html_e('Loading log entries...', 'simple-debugging'); ?></td></tr>
            </tbody>
        </table>
        <div id="sd-log-pagination" style="margin:12px 0 0 0;"></div>
        <?php
    }

}

endif;
