(function () {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-copy]');
        if (!button) return;

        var target = document.querySelector(button.getAttribute('data-copy'));
        if (!target) return;

        var text = target.textContent.trim();
        var done = function () {
            var label = button.textContent;
            button.textContent = button.getAttribute('data-copied-label') || '已复制';
            window.setTimeout(function () { button.textContent = label; }, 1500);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done);
            return;
        }

        var area = document.createElement('textarea');
        area.value = text;
        document.body.appendChild(area);
        area.select();
        try { document.execCommand('copy'); done(); } catch (err) {}
        document.body.removeChild(area);
    });
})();
