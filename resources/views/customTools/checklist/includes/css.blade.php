<style>
    .checklist-page {
        width: 100%;
    }

    .checklist-toolbar,
    .checklist-panel {
        width: 100%;
    }

    .checklist-toolbar {
        align-items: center;
        display: flex;
        justify-content: space-between;
        min-height: 54px;
    }

    .checklist-title {
        color: #333;
        font-size: 16px;
        font-weight: bolder;
        margin: 0;
        text-transform: uppercase;
    }

    .checklist-action {
        color: #666;
        font-weight: bolder;
        text-decoration: none;
        text-transform: uppercase;
    }

    .checklist-action:hover {
        color: dodgerblue;
        text-decoration: none;
    }

    .checklist-tabs {
        display: flex;
        margin-bottom: 10px;
        width: 100%;
    }

    .checklist-tab {
        background-color: transparent;
        border: 1px solid #999;
        color: #333;
        cursor: pointer;
        flex: 1;
        font-weight: bolder;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        text-transform: uppercase;
    }

    .checklist-tab.is-active,
    .checklist-tab:hover {
        background-color: dodgerblue;
        color: #fff;
        text-decoration: none;
    }

    .checklist-list {
        border-collapse: collapse;
        width: 100%;
    }

    .checklist-list th,
    .checklist-list td {
        border-bottom: 1px solid #ddd;
        padding: 9px 8px;
        vertical-align: middle;
    }

    .checklist-list th {
        color: #333;
        font-size: 12px;
        font-weight: bolder;
        text-transform: uppercase;
    }

    .checklist-row-done {
        background: #ddd;
    }

    .checklist-row-muted {
        color: #999;
    }

    .checklist-icon-link,
    .checklist-icon-button {
        background: transparent;
        border: 0;
        cursor: pointer;
        display: inline-block;
        padding: 0 5px;
    }

    .checklist-form-table {
        width: 100%;
    }

    .checklist-form-table td {
        padding: 7px 6px;
        vertical-align: middle;
    }

    .checklist-form-table .left_column {
        font-weight: bolder;
        padding-right: 10px !important;
        text-align: right;
        text-transform: uppercase;
        width: 28%;
    }

    .checklist-form-table .right_column {
        text-align: left;
        width: 72%;
    }

    .checklist-form-table input[type="text"],
    .checklist-form-table input[type="date"],
    .checklist-form-table select,
    .checklist-form-table textarea {
        border: 1px solid #999;
        padding: 6px;
        width: 100%;
    }

    .checklist-checkbox {
        cursor: pointer;
        height: 26px;
        outline: 1px solid #f00 !important;
        width: 26px;
    }

    .checklist-checkbox:checked {
        accent-color: #28a745;
        outline: 1px solid #28a745 !important;
    }

    .checklist-status-dot {
        display: inline-block;
        height: 18px;
        width: 18px;
    }

    @media (max-width: 767px) {
        .checklist-toolbar {
            align-items: flex-start;
            gap: 10px;
            flex-direction: column;
        }

        .checklist-tabs {
            flex-direction: column;
        }

        .checklist-form-table .left_column,
        .checklist-form-table .right_column {
            display: block;
            text-align: left;
            width: 100%;
        }
    }
</style>
