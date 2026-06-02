<style>
    @page { size: A4; margin: 10mm; }
    * { box-sizing: border-box; }
    html { background: #eef1f5; }
    body {
        margin: 0;
        font-family: "Tajawal", "Arial", sans-serif;
        color: #111827;
        line-height: 1.55;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    html[dir="rtl"] body,
    [dir="rtl"] .resume-page { direction: rtl; text-align: right; }
    html[dir="ltr"] body,
    [dir="ltr"] .resume-page { direction: ltr; text-align: left; }
    .resume-page {
        width: 210mm;
        min-height: 297mm;
        margin: 18px auto;
        background: #fff;
        padding: 18mm;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
    }
    .resume-section { break-inside: avoid; page-break-inside: avoid; margin-top: 18px; }
    .resume-section h2 { margin: 0 0 8px; font-size: 14px; letter-spacing: 0; color: var(--accent, #1f6feb); }
    .resume-item { break-inside: avoid; page-break-inside: avoid; margin-bottom: 10px; }
    .muted { color: #6b7280; }
    .small { font-size: 12px; }
    .skill-list { display: flex; flex-wrap: wrap; gap: 6px; }
    .skill-pill { border: 1px solid #dbe4f0; border-radius: 999px; padding: 3px 8px; font-size: 12px; }
    .links { display: flex; flex-wrap: wrap; gap: 8px; }
    html[dir="rtl"] .resume-contact,
    html[dir="rtl"] .skill-list,
    html[dir="rtl"] .links,
    [dir="rtl"] .resume-contact,
    [dir="rtl"] .skill-list,
    [dir="rtl"] .links { justify-content: flex-start; }
    html[dir="ltr"] .resume-contact,
    html[dir="ltr"] .skill-list,
    html[dir="ltr"] .links,
    [dir="ltr"] .resume-contact,
    [dir="ltr"] .skill-list,
    [dir="ltr"] .links { justify-content: flex-start; }
    a { color: inherit; text-decoration: none; }
    @media print {
        html { background: #fff; }
        .resume-page { width: auto; min-height: auto; margin: 0; box-shadow: none; padding: 0; }
        .no-print { display: none !important; }
    }
    @media (max-width: 800px) {
        .resume-page { width: 100%; min-height: auto; margin: 0; padding: 20px; box-shadow: none; }
    }
</style>
