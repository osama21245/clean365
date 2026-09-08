(function ($) {
    ("use strict");

    const globalFallbackImage = '/public/assets/placeholder.png';
    const placeholderAuditStorageKey = 'placeholderFallbackAuditReport';
    const placeholderAuditEnabledKey = 'placeholderFallbackAuditEnabled';
    const emptyPlaceholderAuditReport = () => ({
        totalHits: 0,
        pages: {}
    });
    const readPlaceholderAuditReport = () => {
        try {
            const rawReport = window.localStorage.getItem(placeholderAuditStorageKey);

            if (!rawReport) {
                return emptyPlaceholderAuditReport();
            }

            const parsedReport = JSON.parse(rawReport);
            return parsedReport && typeof parsedReport === 'object'
                ? {
                    totalHits: Number(parsedReport.totalHits) || 0,
                    pages: parsedReport.pages && typeof parsedReport.pages === 'object' ? parsedReport.pages : {}
                }
                : emptyPlaceholderAuditReport();
        } catch (error) {
            return emptyPlaceholderAuditReport();
        }
    };
    const writePlaceholderAuditReport = (report) => {
        try {
            window.localStorage.setItem(placeholderAuditStorageKey, JSON.stringify(report));
        } catch (error) {
            //
        }
    };
    const setPlaceholderAuditEnabled = (enabled) => {
        try {
            if (enabled) {
                window.localStorage.setItem(placeholderAuditEnabledKey, '1');
            } else {
                window.localStorage.removeItem(placeholderAuditEnabledKey);
            }
        } catch (error) {
            //
        }
    };
    const getCurrentPlaceholderAuditPageUrl = () => {
        return `${window.location.pathname}${window.location.search}`;
    };
    const isPlaceholderAuditEnabled = () => {
        try {
            const url = new URL(window.location.href);
            const auditFlag = url.searchParams.get('placeholder_audit');

            if (auditFlag === '1') {
                setPlaceholderAuditEnabled(true);
                return true;
            }

            if (auditFlag === '0') {
                setPlaceholderAuditEnabled(false);
                return false;
            }

            return window.localStorage.getItem(placeholderAuditEnabledKey) === '1';
        } catch (error) {
            return false;
        }
    };
    const isPlaceholderImageSource = (src) => {
        if (!src || typeof src !== 'string') {
            return false;
        }

        return src.includes('/placeholder.png');
    };
    const recordPlaceholderAuditHit = ({originalSrc = '', placeholderSrc = '', reason = 'fallback'} = {}) => {
        if (!isPlaceholderAuditEnabled()) {
            return;
        }

        const pageKey = window.location.pathname;
        const pageUrl = getCurrentPlaceholderAuditPageUrl();
        const report = readPlaceholderAuditReport();
        const pageReport = report.pages[pageKey] || {
            path: pageKey,
            title: document.title,
            urls: [],
            brokenSources: [],
            placeholderSources: [],
            hits: 0,
            fallbackHits: 0,
            directPlaceholderHits: 0,
            lastSeenAt: null
        };

        pageReport.title = document.title;
        pageReport.hits += 1;
        pageReport.lastSeenAt = new Date().toISOString();

        if (reason === 'fallback') {
            pageReport.fallbackHits += 1;
        } else {
            pageReport.directPlaceholderHits += 1;
        }

        if (!pageReport.urls.includes(pageUrl)) {
            pageReport.urls.push(pageUrl);
        }

        if (originalSrc && !pageReport.brokenSources.includes(originalSrc)) {
            pageReport.brokenSources.push(originalSrc);
        }

        if (placeholderSrc && !pageReport.placeholderSources.includes(placeholderSrc)) {
            pageReport.placeholderSources.push(placeholderSrc);
        }

        report.totalHits += 1;
        report.pages[pageKey] = pageReport;
        writePlaceholderAuditReport(report);
    };
    const trackPlaceholderImage = (img, reason = 'direct') => {
        if (!(img instanceof HTMLImageElement) || img.dataset.placeholderAuditSeen === 'true') {
            return;
        }

        const placeholderSrc = img.currentSrc || img.getAttribute('src') || img.src || '';

        if (!isPlaceholderImageSource(placeholderSrc)) {
            return;
        }

        img.dataset.placeholderAuditSeen = 'true';
        recordPlaceholderAuditHit({
            originalSrc: reason === 'fallback' ? (img.dataset.placeholderOriginalSrc || '') : '',
            placeholderSrc,
            reason
        });
    };
    const auditCurrentPlaceholderImages = () => {
        if (!isPlaceholderAuditEnabled()) {
            return;
        }

        document.querySelectorAll('img').forEach((img) => {
            trackPlaceholderImage(img, 'direct');
        });
    };
    const getPlaceholderAuditSummary = () => {
        const report = readPlaceholderAuditReport();
        const pages = Object.values(report.pages)
            .sort((firstPage, secondPage) => secondPage.hits - firstPage.hits);

        return {
            enabled: isPlaceholderAuditEnabled(),
            totalHits: report.totalHits,
            pageCount: pages.length,
            pages
        };
    };
    window.placeholderAudit = {
        enable() {
            setPlaceholderAuditEnabled(true);
            return getPlaceholderAuditSummary();
        },
        disable() {
            setPlaceholderAuditEnabled(false);
            return getPlaceholderAuditSummary();
        },
        clear() {
            try {
                window.localStorage.removeItem(placeholderAuditStorageKey);
            } catch (error) {
                //
            }

            return getPlaceholderAuditSummary();
        },
        report() {
            return getPlaceholderAuditSummary();
        },
        printReport() {
            const summary = getPlaceholderAuditSummary();
            const rows = summary.pages.map((page) => ({
                page: page.path,
                title: page.title,
                hits: page.hits,
                fallback_hits: page.fallbackHits,
                direct_placeholder_hits: page.directPlaceholderHits,
                urls: page.urls.length,
                broken_images: page.brokenSources.length,
                placeholder_images: page.placeholderSources.length,
                last_seen_at: page.lastSeenAt
            }));

            console.info(`Placeholder fallback appeared on ${summary.pageCount} page(s) across ${summary.totalHits} image fallback(s).`);
            console.table(rows);

            return summary;
        }
    };
    const applyImageFallback = (img) => {
        if (!(img instanceof HTMLImageElement) || img.dataset.fallbackApplied === 'true') {
            return;
        }

        if (img.classList.contains('main-logo') || img.classList.contains('c365-brand-logo') || img.dataset.skipImageFallback === 'true') {
            return;
        }

        const originalSrc = img.currentSrc || img.getAttribute('src') || img.src || '';

        img.dataset.fallbackApplied = 'true';
        img.dataset.placeholderOriginalSrc = originalSrc;
        img.onerror = null;
        img.src = globalFallbackImage;
        trackPlaceholderImage(img, 'fallback');
    };

    const patchBrokenImages = () => {
        document.querySelectorAll('img').forEach((img) => {
            if (img.complete && img.naturalWidth === 0) {
                applyImageFallback(img);
            }
        });
    };

    const showPaymentFailureMessage = () => {
        const url = new URL(window.location.href);
        const paymentFlag = url.searchParams.get('flag');
        const paymentFailureTextElement = document.querySelector('.global-payment-failure-text');
        const modalTitle = paymentFailureTextElement?.dataset?.title || 'Payment failed';
        const modalMessage = paymentFailureTextElement?.dataset?.message || 'Online payment failed. Please try again.';

        if (!['fail', 'failed'].includes(paymentFlag) || typeof bootstrap === 'undefined') {
            return;
        }

        const existingModal = document.getElementById('globalPaymentFailureModal');
        if (!existingModal) {
            const modalMarkup = `
                <div class="modal fade" id="globalPaymentFailureModal" tabindex="-1" aria-labelledby="globalPaymentFailureModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header pb-0 border-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pb-sm-5 px-sm-5">
                                <div class="d-flex flex-column align-items-center gap-2 text-center">
                                    <img src="/public/assets/admin-module/img/payment-failed.png" alt="" width="80" height="80">
                                    <h3 id="globalPaymentFailureModalLabel">${modalTitle}</h3>
                                    <p class="fw-medium">${modalMessage}</p>
                                    <button type="button" class="btn btn--primary" data-bs-dismiss="modal">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalMarkup);
        }

        const modalElement = document.getElementById('globalPaymentFailureModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        url.searchParams.delete('flag');
        window.history.replaceState({}, document.title, url.toString());
    };

    document.addEventListener('error', function (event) {
        applyImageFallback(event.target);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            patchBrokenImages();
            auditCurrentPlaceholderImages();
        });
    } else {
        patchBrokenImages();
        auditCurrentPlaceholderImages();
    }

    window.addEventListener('load', function () {
        patchBrokenImages();
        auditCurrentPlaceholderImages();
    });

    $(document).ready(function () {
        showPaymentFailureMessage();

        // When Select2 opens
        $(document).on('select2:open', function () {
            $('.down-icon').addClass('active');
        });

        // When Select2 closes
        $(document).on('select2:close', function () {
            $('.down-icon').removeClass('active');
        });
    });



    // --- Fixed Action Button ---
    let isFixed = false;

    function checkContentHeight() {
        let windowHeight = $(window).height();
        let contentHeight = $(document).height();
        let scrollPosition = $(window).scrollTop();
        let $actionWrapper = $(".action-btn-wrapper");
        let $parent = $actionWrapper.parent();

        setTimeout(() => {
            if (contentHeight > windowHeight) {
                if (!isFixed) {
                    $parent.addClass("fixed-bottom");
                    $actionWrapper.addClass("fixed");
                    isFixed = true;
                }

                if (scrollPosition + windowHeight >= contentHeight - 100) {
                    if (isFixed) {
                        $actionWrapper.removeClass("fixed");
                        $parent.removeClass("fixed-bottom");
                        isFixed = false;
                    }
                }
            } else {
                if (isFixed) {
                    $actionWrapper.removeClass("fixed");
                    $parent.removeClass("fixed-bottom");
                    isFixed = false;
                }
            }
        }, 500);
    }

    checkContentHeight();

    $(window).on("resize scroll", function() {
        checkContentHeight();
    });

    // --- Easy setup guide
    $(document).ready(function () {
        $('.view-guideline-btn').on('click', function () {
            $('.easy-setup-dropdown').addClass('show');
            $(this).removeClass('show');
        });

        $('.easy-setup-dropdown_close').on('click', function () {
            $('.easy-setup-dropdown').removeClass('show');
            $('.view-guideline-btn').addClass('show');
        });
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.easy-setup-dropdown, .view-guideline-btn').length) {
            $('.easy-setup-dropdown').removeClass('show');
            $('.view-guideline-btn').addClass('show');
        }
    });


})(jQuery);
