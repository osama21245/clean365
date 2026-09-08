window.addEventListener("load", function () {
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }
    function highlightText(keyword) {
        if (!keyword) return;

        const regex = new RegExp(`(${keyword})`, "gi");

        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    const parent = node.parentNode;
                    if (
                        parent &&
                        parent.nodeName !== "SCRIPT" &&
                        parent.nodeName !== "STYLE" &&
                        !parent.closest("mark") &&
                        !parent.closest(".material-icons") &&  //New Code
                        node.nodeValue.trim().length > 0
                    ) {
                        return NodeFilter.FILTER_ACCEPT;
                    }
                    return NodeFilter.FILTER_REJECT;
                }
            }
        );

        const nodesToReplace = [];

        while (walker.nextNode()) {
            const node = walker.currentNode;
            if (regex.test(node.nodeValue)) {
                nodesToReplace.push(node);
            }
        }

        nodesToReplace.forEach(node => {
            const span = document.createElement("span");
            span.innerHTML = node.nodeValue.replace(regex, '<mark>$1</mark>');
            node.parentNode.replaceChild(span, node);
        });
    }

    function showFirstHighlight() {
        const getFirstVisibleMark = () => {
            const marks = Array.from(document.querySelectorAll("main.main-area mark, .main-content mark, mark"));

            return marks.find((mark) => {
                const style = window.getComputedStyle(mark);
                const rect = mark.getBoundingClientRect();
                const hiddenParent = mark.closest('[hidden], .d-none, [aria-hidden="true"]');

                return !hiddenParent
                    && style.display !== "none"
                    && style.visibility !== "hidden"
                    && rect.width > 0
                    && rect.height > 0;
            }) || null;
        };

        const firstMark = getFirstVisibleMark();
        let scrollAnimationFrameId = null;

        if (!firstMark) {
            return;
        }

        const scrollingElement = document.scrollingElement || document.documentElement;
        const getCurrentScrollTop = () => (
            scrollingElement?.scrollTop
            ?? window.pageYOffset
            ?? document.documentElement.scrollTop
            ?? 0
        );

        const setScrollTop = (value) => {
            if (scrollingElement) {
                scrollingElement.scrollTop = value;
                return;
            }

            window.scrollTo(0, value);
        };

        const animateScrollTo = (targetTop, duration = 900) => {
            if (scrollAnimationFrameId) {
                cancelAnimationFrame(scrollAnimationFrameId);
            }

            const startTop = getCurrentScrollTop();
            const distance = targetTop - startTop;

            if (Math.abs(distance) < 2) {
                setScrollTop(targetTop);
                return;
            }

            const startTime = performance.now();
            const easeInOutQuad = (progress) => (
                progress < 0.5
                    ? 2 * progress * progress
                    : 1 - Math.pow(-2 * progress + 2, 2) / 2
            );

            const step = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easedProgress = easeInOutQuad(progress);

                setScrollTop(startTop + (distance * easedProgress));

                if (progress < 1) {
                    scrollAnimationFrameId = requestAnimationFrame(step);
                } else {
                    scrollAnimationFrameId = null;
                }
            };

            scrollAnimationFrameId = requestAnimationFrame(step);
        };

        const getBottomOverlayHeight = () => {
            const selectors = [
                ".action-btn-wrapper",
                ".sticky-bottom",
                ".fixed-bottom",
                ".btn-fixed-bottom",
                ".save-button-wrapper"
            ];

            let maxHeight = 0;

            selectors.forEach((selector) => {
                document.querySelectorAll(selector).forEach((element) => {
                    const style = window.getComputedStyle(element);
                    const rect = element.getBoundingClientRect();
                    const isVisible = style.display !== "none" && style.visibility !== "hidden" && rect.width > 0 && rect.height > 0;
                    const isBottomOverlay = ["fixed", "sticky"].includes(style.position) || rect.bottom >= (window.innerHeight - 40);

                    if (isVisible && isBottomOverlay) {
                        maxHeight = Math.max(maxHeight, rect.height);
                    }
                });
            });

            return maxHeight;
        };

        const getTargetTop = () => {
            const freshFirstMark = getFirstVisibleMark() || firstMark;
            const markRect = freshFirstMark.getBoundingClientRect();
            const bottomOverlayHeight = getBottomOverlayHeight();
            const viewportPaddingTop = 120;
            const viewportPaddingBottom = bottomOverlayHeight > 0 ? bottomOverlayHeight + 24 : 40;
            const absoluteTop = markRect.top + window.scrollY;
            const absoluteBottom = markRect.bottom + window.scrollY;
            const visibleViewportHeight = window.innerHeight - viewportPaddingTop - viewportPaddingBottom;

            let targetTop = absoluteTop - viewportPaddingTop;

            if (visibleViewportHeight > 0 && (absoluteBottom - absoluteTop) < visibleViewportHeight) {
                targetTop = absoluteTop - viewportPaddingTop - ((visibleViewportHeight - (absoluteBottom - absoluteTop)) / 2);
            }

            const maxScrollTop = Math.max((scrollingElement?.scrollHeight || document.body.scrollHeight) - window.innerHeight, 0);
            return Math.min(Math.max(targetTop, 0), maxScrollTop);
        };

        const scrollToFirstHighlight = () => {
            animateScrollTo(getTargetTop());
        };

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                scrollToFirstHighlight();
            });
        });
    }

    const keyword = getQueryParam("keyword");
    if (keyword) {
        highlightText(keyword);
        showFirstHighlight();
    }
});
