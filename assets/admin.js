(function ($) {
    let page = 1, size = 25, total = 0, pages = 1;
    let allRows = [], sortCol = 'timestamp', sortDir = 'desc', filterTxt = '';

    function loadLog(p = 1, s = null) {
        if (s) size = parseInt(s, 10);
        page = p;
        $('#sd-log-table tbody').html('<tr><td colspan="5">' + SD_DEBUG.i18n.loading + '</td></tr>');
        $.post(SD_DEBUG.ajaxurl, {
            action: 'sd_load_log',
            paged: page,
            per_page: size,
            _wpnonce: SD_DEBUG.nonce
        }, function (data) {
            if (data && data.rows) {
                allRows = data.rows;
                total = data.total;
                pages = Math.ceil(total / size);
                renderTable();
            } else {
                $('#sd-log-table tbody').html('<tr><td colspan="5">' + SD_DEBUG.i18n.none + '</td></tr>');
                total = 0; pages = 1;
                updatePagination();
            }
        });
    }

    function renderTable() {
        let rows = allRows.slice();
        // Filtering
        let f = filterTxt.trim().toLowerCase();
        if (f) {
            rows = rows.filter(row =>
                Object.values(row).join(' ').toLowerCase().includes(f)
            );
        }
        // Sorting
        rows.sort((a, b) => {
            let x = a[sortCol] || '', y = b[sortCol] || '';
            if (sortCol === 'line') { x = parseInt(x) || 0; y = parseInt(y) || 0; }
            if (x < y) return sortDir === 'asc' ? -1 : 1;
            if (x > y) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });
        // Build html
        let html = '';
        rows.forEach(function (row) {
            html += '<tr>'
                + '<td class="column-primary" style="white-space:nowrap;">' + row.timestamp + '</td>'
                + '<td>' + row.title + '</td>'
                + '<td>' + row.file + '</td>'
                + '<td>' + row.line + '</td>'
                + '<td>' + row.description + '</td>'
                + '</tr>';
        });
        if (!html) html = '<tr><td colspan="5">' + SD_DEBUG.i18n.none + '</td></tr>';
        $('#sd-log-table tbody').html(html);
        updateSortIcons();
        updatePagination(rows.length);
    }

    function updatePagination(count) {
        let html = '';
        if (total > 0) {
            html += SD_DEBUG.i18n.page + ' ' + page + ' ' + SD_DEBUG.i18n.of + ' ' + pages + ' &nbsp; ';
            if (page > 1) html += '<a href="#" data-page="' + (page - 1) + '">&laquo; ' + SD_DEBUG.i18n.prev + '</a> ';
            for (let i = 1; i <= pages; i++) {
                if (i == page) html += '<b>' + i + '</b> ';
                else if (i <= 2 || i > pages - 2 || Math.abs(i - page) <= 2) html += '<a href="#" data-page="' + i + '">' + i + '</a> ';
                else if (i == page - 3 || i == page + 3) html += '... ';
            }
            if (page < pages) html += '<a href="#" data-page="' + (page + 1) + '">' + SD_DEBUG.i18n.next + ' &raquo;</a>';
        }
        $('#sd-log-pagination').html(html);
    }

    function updateSortIcons() {
        $('#sd-log-table th .sd-sort-icon').html('');
        $('#sd-log-table th[data-col]').each(function () {
            var $th = $(this);
            var col = $th.data('col');
            if (col === sortCol) {
                $th.find('.sd-sort-icon').html(sortDir === 'asc' ? '▲' : '▼');
            } else {
                $th.find('.sd-sort-icon').html('');
            }
        });
    }

    $(document).on('change', '#sd-log-size', function () {
        loadLog(1, $(this).val());
    });
    $(document).on('click', '#sd-log-pagination a', function (e) {
        e.preventDefault();
        let p = parseInt($(this).attr('data-page'));
        if (p) loadLog(p);
    });
    $(document).on('keyup', '#sd-log-filter', function () {
        filterTxt = $(this).val();
        renderTable();
    });
    $(document).on('click', '#sd-log-table th[data-col]', function () {
        let col = $(this).data('col');
        if (col === sortCol) sortDir = (sortDir === 'asc') ? 'desc' : 'asc';
        else { sortCol = col; sortDir = 'asc'; }
        renderTable();
    });

    $(function () { loadLog(); });

})(jQuery);
