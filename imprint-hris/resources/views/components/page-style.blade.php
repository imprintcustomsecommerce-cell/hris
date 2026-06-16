<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: Inter, Arial, sans-serif;
        background: #f6f7fb;
        color: #111827;
    }

    .page {
        min-height: calc(100vh - 78px);
        padding: 50px 32px;
        background:
            radial-gradient(circle at top left, rgba(17, 24, 39, 0.08), transparent 35%),
            #f6f7fb;
    }

    .success-alert {
        max-width: 1280px;
        margin: 0 auto 20px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: 16px 20px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 800;
    }

    .error-alert {
        max-width: 1280px;
        margin: 20px auto 0;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 16px 20px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 700;
    }

    .error-alert strong { display: block; margin-bottom: 8px; }
    .error-alert ul { margin: 0; padding-left: 20px; }

    .page-header {
        max-width: 1280px;
        margin: 0 auto 28px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
    }

    .badge {
        display: inline-block;
        background: #111827;
        color: white;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .page-header h1 {
        font-size: clamp(36px, 5vw, 56px);
        margin: 0 0 12px;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 17px;
        line-height: 1.6;
    }

    .add-btn {
        background: #111827;
        color: white;
        text-decoration: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-weight: 800;
        white-space: nowrap;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .18);
    }

    .back-btn {
        background: white;
        color: #111827;
        text-decoration: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-weight: 800;
        border: 1px solid #e5e7eb;
        white-space: nowrap;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .08);
    }

    .summary-grid {
        max-width: 1280px;
        margin: 0 auto 24px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .07);
    }

    .summary-card span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .summary-card strong {
        font-size: 32px;
        color: #111827;
    }

    .panel {
        max-width: 1280px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 28px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .panel h2 {
        margin: 0 0 6px;
        font-size: 24px;
    }

    .panel .panel-sub {
        margin: 0 0 24px;
        color: #6b7280;
        font-size: 14px;
    }

    .table-wrap { width: 100%; overflow-x: auto; }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    th {
        text-align: left;
        padding: 14px 16px;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    tr:last-child td { border-bottom: none; }

    .pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #f3f4f6;
        color: #374151;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .status.green  { background: #dcfce7; color: #166534; }
    .status.amber  { background: #fef3c7; color: #92400e; }
    .status.red    { background: #fee2e2; color: #991b1b; }
    .status.blue   { background: #dbeafe; color: #1e40af; }
    .status.gray   { background: #f3f4f6; color: #374151; }

    .actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .actions a, .actions button {
        text-decoration: none;
        color: #111827;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }

    .actions a:hover, .actions button:hover { background: #111827; color: white; }
    .actions .del:hover { background: #991b1b; }
    .actions form { margin: 0; }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 42px; margin-bottom: 16px; }
    .empty-state h3 { margin: 0 0 8px; font-size: 22px; color: #111827; }
    .empty-state p { margin: 0 0 22px; color: #6b7280; }

    /* Forms */
    .form-panel {
        max-width: 1280px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 34px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group.full { grid-column: span 2; }
    .form-group label { font-size: 13px; font-weight: 800; color: #374151; }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 14px 16px;
        border-radius: 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        font-family: inherit;
    }

    .form-group textarea { resize: vertical; }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #111827;
        background: white;
        box-shadow: 0 0 0 4px rgba(17, 24, 39, .06);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 24px;
    }

    .cancel-btn, .save-btn {
        border: none;
        text-decoration: none;
        padding: 14px 22px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
    }

    .cancel-btn { background: #f3f4f6; color: #111827; }
    .save-btn { background: #111827; color: white; box-shadow: 0 16px 35px rgba(17, 24, 39, .18); }
    .save-btn:hover { background: #030712; }

    /* Detail view */
    .detail-grid {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .detail-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .07);
    }

    .detail-card.full { grid-column: span 2; }
    .detail-card h2 { margin: 0 0 20px; font-size: 20px; }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    }

    .detail-row:last-child { border-bottom: none; }
    .detail-row span { color: #6b7280; font-weight: 700; }
    .detail-row strong { color: #111827; text-align: right; }

    @media (max-width: 1000px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .summary-grid { grid-template-columns: repeat(2, 1fr); }
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full { grid-column: span 1; }
        .detail-grid { grid-template-columns: 1fr; }
        .detail-card.full { grid-column: span 1; }
    }

    @media (max-width: 600px) {
        .page { padding: 36px 20px; }
        .summary-grid { grid-template-columns: 1fr; }
        .panel, .form-panel { padding: 22px; border-radius: 24px; }
        .add-btn, .back-btn { width: 100%; text-align: center; }
    }
</style>
