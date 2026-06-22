<style>
    .asg-page {
        background: #f4f6f8;
        min-height: calc(100vh - 60px);
    }

    .asg-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .asg-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        padding: 18px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .asg-card-title {
        font-weight: 700;
        font-size: 15px;
        margin-bottom: 14px;
        color: #202733;
    }

    .asg-alert {
        border-radius: 6px;
    }

    .asg-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 16px;
    }

    .asg-event-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        overflow: hidden;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    .asg-event-card:hover {
        box-shadow: 0 8px 20px rgba(16, 24, 40, .08);
        transform: translateY(-1px);
    }

    .asg-event-media {
        position: relative;
        height: 145px;
        background: #edf0f3;
    }

    .asg-event-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .asg-event-placeholder {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 13px;
    }

    .asg-event-status {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
    }

    .asg-event-body {
        padding: 12px;
    }

    .asg-event-title {
        font-weight: 700;
        color: #202733;
        font-size: 14px;
        line-height: 1.3;
        min-height: 36px;
    }

    .asg-event-subtitle {
        color: #6c757d;
        font-size: 12px;
        margin-top: 4px;
        min-height: 18px;
    }

    .asg-event-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        margin: 12px 0;
        font-size: 12px;
        color: #596274;
    }

    .asg-event-actions {
        border-top: 1px solid #edf0f3;
        padding-top: 10px;
    }

    .asg-empty {
        background: #fff;
        border: 1px dashed #c9ced6;
        padding: 28px;
        text-align: center;
        color: #6c757d;
        border-radius: 6px;
        grid-column: 1 / -1;
    }

    .asg-layout {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .asg-sticky {
        position: sticky;
        top: 16px;
    }

    .asg-nav-link {
        display: block;
        color: #344054;
        text-decoration: none;
        padding: 9px 10px;
        border-radius: 5px;
        margin-bottom: 4px;
        background: #f8f9fa;
        border: 1px solid #eef1f4;
        font-size: 13px;
    }

    .asg-nav-link:hover {
        background: #eef4ff;
        color: #0d6efd;
    }

    .asg-summary {
        display: grid;
        gap: 8px;
    }

    .asg-summary div {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 13px;
        border-bottom: 1px solid #edf0f3;
        padding-bottom: 7px;
    }

    .asg-summary span {
        color: #6c757d;
    }

    .asg-lang-tabs .nav-link {
        border: 1px solid #dfe3e8;
        color: #344054;
        margin-right: 6px;
        background: #fff;
    }

    .asg-lang-tabs .nav-link.active {
        color: #fff;
    }

    .asg-upload-box {
        border: 1px solid #e1e5ea;
        background: #fbfcfd;
        border-radius: 6px;
        padding: 14px;
        min-height: 100%;
    }

    .asg-preview img {
        max-height: 220px;
        object-fit: contain;
        width: 100%;
        background: #fff;
        border: 1px solid #e1e5ea;
        border-radius: 5px;
    }

    .asg-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
    }

    .asg-gallery-item {
        position: relative;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        background: #fff;
        padding: 6px;
        cursor: grab;
        user-select: none;
    }

    .asg-gallery-item:active {
        cursor: grabbing;
    }

    .asg-gallery-item.is-dragging {
        opacity: .45;
        border-style: dashed;
    }

    .asg-gallery-item.is-drag-over {
        box-shadow: 0 0 0 2px #0d6efd;
    }

    .asg-gallery-item img {
        width: 100%;
        height: 78px;
        object-fit: cover;
        border-radius: 4px;
        display: block;
    }

    .asg-gallery-order {
        position: absolute;
        top: 4px;
        left: 4px;
        min-width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(32, 39, 51, .88);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .asg-gallery-drag {
        position: absolute;
        right: 6px;
        top: 4px;
        width: 26px;
        height: 26px;
        border-radius: 5px;
        background: rgba(255, 255, 255, .92);
        color: #596274;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        z-index: 2;
        border: 1px solid rgba(0,0,0,.12);
    }

    .asg-gallery-footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 28px;
        gap: 6px;
        align-items: center;
        margin-top: 6px;
    }

    .asg-gallery-remove {
        width: 28px;
        height: 28px;
        border: 0;
        border-radius: 5px;
        background: #fff1f2;
        color: #b42318;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .asg-gallery-path {
        font-size: 11px;
        color: #6c757d;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    @media (max-width: 992px) {
        .asg-layout {
            grid-template-columns: 1fr;
        }

        .asg-sticky {
            position: static;
        }
    }

    @media (max-width: 576px) {
        .asg-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
