import $ from 'jquery';
import 'datatables.net-dt';

window.jQuery = window.$ = $;

export function initDataTable(selector, options = {}) {
    const $table = $(selector);

    if (!$table.length || $.fn.DataTable.isDataTable($table)) {
        return;
    }

    return $table.DataTable({
        columnDefs: [
            { targets: [0, 3, 4], orderable: false },
        ],
        order: [],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: '',
            searchPlaceholder: 'Search...',
        },
        ...options,
    });
}