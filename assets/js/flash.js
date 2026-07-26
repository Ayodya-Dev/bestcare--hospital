/**
 * Auto-hide flash / alert messages across BestCare Hospital.
 * Also strips ?msg= from the URL so refresh does not bring the banner back.
 */
(function () {
    function stripMsgParam() {
        if (!window.history || !window.history.replaceState) {
            return;
        }
        if (location.search.indexOf("msg=") === -1) {
            return;
        }
        try {
            var url = new URL(location.href);
            url.searchParams.delete("msg");
            var next = url.pathname + (url.searchParams.toString() ? "?" + url.searchParams.toString() : "") + url.hash;
            window.history.replaceState({}, document.title, next);
        } catch (e) {
            // Older browsers / odd URLs — still try a simple strip
            window.history.replaceState({}, document.title, location.pathname);
        }
    }

    function hideEl(el) {
        if (!el || el.getAttribute("data-flash-done") === "1") {
            return;
        }
        el.setAttribute("data-flash-done", "1");
        el.style.transition = "opacity 0.4s ease, max-height 0.4s ease, margin 0.4s ease, padding 0.4s ease";
        el.style.opacity = "0";
        el.style.maxHeight = "0";
        el.style.marginTop = "0";
        el.style.marginBottom = "0";
        el.style.paddingTop = "0";
        el.style.paddingBottom = "0";
        el.style.overflow = "hidden";
        setTimeout(function () {
            if (el && el.parentNode) {
                el.style.display = "none";
            }
        }, 420);
    }

    function run() {
        stripMsgParam();

        var selectors = [
            ".adm-alert",
            ".sd-alert",
            ".pq-alert",
            ".cp-alert",
            ".ct-alert",
            ".error-msg",
            ".success-msg",
            ".ba-alert",
            ".pr-flash",
            "#admFlash"
        ];

        var nodes = document.querySelectorAll(selectors.join(","));
        if (!nodes.length) {
            return;
        }

        // Success / info hide sooner; errors stay a bit longer so users can read them
        for (var i = 0; i < nodes.length; i++) {
            (function (el) {
                var isErr = el.classList.contains("err")
                    || el.classList.contains("error")
                    || el.classList.contains("error-msg");
                var delay = isErr ? 6000 : 3500;
                setTimeout(function () {
                    hideEl(el);
                }, delay);
            })(nodes[i]);
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", run);
    } else {
        run();
    }
})();
