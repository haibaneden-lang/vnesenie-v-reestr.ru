(function () {
    'use strict';

    var progressEl = document.getElementById('articleReadProgress');
    if (progressEl) {
        function updateProgress() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var pct = height > 0 ? Math.min(100, Math.round((winScroll / height) * 100)) : 0;
            progressEl.style.width = pct + '%';
            progressEl.setAttribute('aria-valuenow', pct);
        }
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    var tocList = document.querySelector('.article-toc-list');
    if (!tocList) return;
    var tocLinks = tocList.querySelectorAll('a[href^="#"]');
    var headings = [];
    tocLinks.forEach(function (a) {
        var id = (a.getAttribute('href') || '').slice(1);
        if (id) {
            var heading = document.getElementById(id);
            if (heading) headings.push({ id: id, link: a, top: 0 });
        }
    });

    function getTop(el) {
        if (!el) return 0;
        var r = el.getBoundingClientRect();
        return (r.top + (window.pageYOffset || document.documentElement.scrollTop));
    }
    function updateTocActive() {
        var scrollY = window.pageYOffset || document.documentElement.scrollTop;
        var viewportMid = scrollY + window.innerHeight * 0.2;
        var activeId = null;
        headings.forEach(function (h) {
            var el = document.getElementById(h.id);
            var top = getTop(el);
            if (top <= viewportMid) activeId = h.id;
        });
        tocLinks.forEach(function (link) {
            var id = (link.getAttribute('href') || '').slice(1);
            link.classList.toggle('is-active', id === activeId);
        });
    }

    if (headings.length) {
        window.addEventListener('scroll', updateTocActive, { passive: true });
        updateTocActive();
    }
})();
