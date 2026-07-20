{{--
    WCAG Contrast Checker — client-side component.
    Reads color input values, calculates contrast ratios, and shows warnings.
    Requires SweetAlert2 (loaded in layouts.sec).
--}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contrastRules = [
        { fg: 'sidebar_brand_text_color', bg: 'sidebar_background_color', label: 'Sidebar brand text', min: 4.5 },
        { fg: 'sidebar_text_color', bg: 'sidebar_background_color', label: 'Sidebar text', min: 4.5 },
        { fg: 'sidebar_hover_text_color', bg: 'sidebar_hover_background_color', label: 'Sidebar hover text', min: 4.5 },
        { fg: 'table_header_text_color', bg: 'table_header_color', label: 'Table header text', min: 4.5 },
    ];

    function relativeLuminance(hex) {
        const channels = [];
        for (let i = 1; i < 7; i += 2) {
            let c = parseInt(hex.substring(i, i + 2), 16) / 255;
            c = c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
            channels.push(c);
        }
        return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
    }

    function contrastRatio(fg, bg) {
        const l1 = relativeLuminance(fg);
        const l2 = relativeLuminance(bg);
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        return (lighter + 0.05) / (darker + 0.05);
    }

    window.checkContrast = function () {
        const failures = [];

        contrastRules.forEach(function (rule) {
            const fgInput = document.getElementById(rule.fg);
            const bgInput = document.getElementById(rule.bg);

            if (!fgInput || !bgInput) return;

            const fg = fgInput.value.trim();
            const bg = bgInput.value.trim();

            if (!/^#[0-9A-Fa-f]{6}$/.test(fg) || !/^#[0-9A-Fa-f]{6}$/.test(bg)) return;

            const ratio = contrastRatio(fg, bg);

            if (ratio < rule.min) {
                failures.push(
                    rule.label + ': ' + fg + ' on ' + bg +
                    ' has a contrast ratio of ' + ratio.toFixed(2) + ':1, ' +
                    'below the minimum ' + rule.min + ':1.'
                );
            }
        });

        if (failures.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'WCAG Contrast Warnings',
                    html: failures.map(function (f) { return '<div class="text-start mb-1">• ' + f + '</div>'; }).join(''),
                    confirmButtonText: 'Review colors',
                });
            } else {
                alert('WCAG Contrast Warnings:\n\n' + failures.join('\n'));
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'All contrast checks passed!',
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        }

        return failures.length === 0;
    };
});
</script>
@endpush