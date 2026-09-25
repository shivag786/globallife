/**
 * Bulk selection for the permission grid.
 *
 * Three levels of toggle, so a grid never has to be ticked cell by cell:
 *   [data-permission-all]              every box
 *   [data-permission-row="module"]     every action for one module
 *   [data-permission-column="action"]  one action across every module
 *
 * The row/column/all boxes are UI only — they carry no name and are never
 * submitted. They also reflect the grid: tick every cell in a row by hand and
 * that row's toggle turns itself on, and a partly-filled row shows as
 * indeterminate rather than pretending to be off.
 */
export function initPermissionMatrix() {
    document.querySelectorAll('[data-permission-matrix]').forEach(setup);
}

function setup(root) {
    const boxes = [...root.querySelectorAll('[data-permission-box]')];
    if (boxes.length === 0) return;

    const all = root.querySelector('[data-permission-all]');
    const rows = [...root.querySelectorAll('[data-permission-row]')];
    const columns = [...root.querySelectorAll('[data-permission-column]')];

    const inRow = (module) => boxes.filter((b) => b.dataset.module === module);
    const inColumn = (action) => boxes.filter((b) => b.dataset.action === action);

    /** Reflect a group's state onto its toggle: on, off, or partly filled. */
    function reflect(toggle, group) {
        const checked = group.filter((b) => b.checked).length;
        toggle.checked = checked === group.length && group.length > 0;
        toggle.indeterminate = checked > 0 && checked < group.length;
    }

    function refreshToggles() {
        rows.forEach((t) => reflect(t, inRow(t.dataset.permissionRow)));
        columns.forEach((t) => reflect(t, inColumn(t.dataset.permissionColumn)));
        if (all) reflect(all, boxes);
    }

    function apply(group, checked) {
        group.forEach((b) => {
            b.checked = checked;
        });
        refreshToggles();
    }

    all?.addEventListener('change', () => apply(boxes, all.checked));
    rows.forEach((t) => t.addEventListener('change', () => apply(inRow(t.dataset.permissionRow), t.checked)));
    columns.forEach((t) =>
        t.addEventListener('change', () => apply(inColumn(t.dataset.permissionColumn), t.checked))
    );
    boxes.forEach((b) => b.addEventListener('change', refreshToggles));

    // Start in step with whatever is already granted.
    refreshToggles();
}
