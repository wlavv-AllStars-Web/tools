import './bootstrap';

const panelListText = (value) => String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase();

const panelListValue = (cell) => (cell?.innerText ?? cell?.textContent ?? '').trim();

const panelListComparableValue = (value) => {
    const dateMatch = value.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})(?:\s+(\d{1,2}):(\d{2}))?$/);
    if (dateMatch) {
        const [, day, month, year, hours = '0', minutes = '0'] = dateMatch;
        return new Date(Number(year.length === 2 ? `20${year}` : year), Number(month) - 1, Number(day), Number(hours), Number(minutes)).getTime();
    }

    const isNumericValue = /^[+-]?(?:[€$£]\s*)?\d[\d\s.,]*(?:%|\s*[€$£])?$/.test(value);
    const normalized = value.replace(/\s/g, '').replace(/[^\d,.-]/g, '');

    if (isNumericValue && /^-?[\d.]+,[\d]+$/.test(normalized)) {
        return Number(normalized.replace(/\./g, '').replace(',', '.'));
    }

    if (isNumericValue && /^-?[\d,]+(?:\.\d+)?$/.test(normalized)) {
        return Number(normalized.replace(',', '.'));
    }

    return panelListText(value);
};

const initialisePanelList = (table) => {
    if (table.dataset.ntPanelListReady === 'true') {
        return;
    }

    const rows = Array.from(table.rows);
    const headerRow = rows[0];

    if (!headerRow || rows.length < 2) {
        return;
    }

    const headerCells = Array.from(headerRow.cells);
    const actionColumns = headerCells.map((cell) => cell.dataset.ntListAction === 'true');
    const filterRow = document.createElement('tr');
    filterRow.className = 'nt-panel-list__filters';

    headerCells.forEach((headerCell, columnIndex) => {
        const filterCell = document.createElement('td');

        if (!actionColumns[columnIndex]) {
            headerCell.classList.add('nt-panel-list__sortable');
            headerCell.tabIndex = 0;
            headerCell.setAttribute('role', 'button');
            headerCell.setAttribute('aria-sort', 'none');

            const filter = document.createElement('input');
            filter.type = 'search';
            filter.className = 'nt-panel-list__filter';
            filter.placeholder = 'Filter';
            filter.setAttribute('aria-label', `Filter ${panelListValue(headerCell)}`);
            filter.addEventListener('click', (event) => event.stopPropagation());
            filter.addEventListener('input', () => {
                Array.from(table.rows).slice(2).forEach((row) => {
                    const matches = headerCells.every((_, index) => {
                        if (actionColumns[index]) {
                            return true;
                        }

                        const query = panelListText(filterRow.cells[index].querySelector('input')?.value);
                        return query === '' || panelListText(panelListValue(row.cells[index])).includes(query);
                    });

                    row.hidden = !matches;
                });
            });
            filterCell.append(filter);

            let direction = 'ascending';
            const sort = () => {
                const bodyRows = Array.from(table.rows).slice(2);
                bodyRows.sort((left, right) => {
                    const leftValue = panelListComparableValue(panelListValue(left.cells[columnIndex]));
                    const rightValue = panelListComparableValue(panelListValue(right.cells[columnIndex]));
                    const compared = typeof leftValue === 'number' && typeof rightValue === 'number'
                        ? leftValue - rightValue
                        : String(leftValue).localeCompare(String(rightValue), undefined, { numeric: true, sensitivity: 'base' });

                    return direction === 'ascending' ? compared : -compared;
                });

                bodyRows.forEach((row) => table.tBodies[0]?.append(row));
                headerCells.forEach((cell, index) => cell.setAttribute('aria-sort', index === columnIndex ? direction : 'none'));
                direction = direction === 'ascending' ? 'descending' : 'ascending';
            };

            headerCell.addEventListener('click', sort);
            headerCell.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sort();
                }
            });
        }

        filterRow.append(filterCell);
    });

    headerRow.after(filterRow);
    table.dataset.ntPanelListReady = 'true';
};

const initialisePanelLists = (root = document) => {
    root.querySelectorAll?.('table[data-nt-panel-list]').forEach(initialisePanelList);
};

document.addEventListener('DOMContentLoaded', () => {
    initialisePanelLists();

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                if (node.matches?.('table[data-nt-panel-list]')) {
                    initialisePanelList(node);
                }

                initialisePanelLists(node);
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
});
