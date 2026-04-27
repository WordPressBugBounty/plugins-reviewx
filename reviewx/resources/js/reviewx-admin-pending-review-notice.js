(function () {
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return;
  }

  var config = window.reviewxPendingReviewNotice || {};
  var interval = Number(config.pollInterval || 15000);
  var refreshEventName = 'reviewx:pending-review-refresh';
  var countEventName = 'reviewx:pending-review-count';
  var lastPendingCount = Number.isFinite(Number(config.initialCount))
    ? Number(config.initialCount)
    : 0;

  function getBadgeCounts() {
    return document.querySelectorAll(
      '#toplevel_page_reviewx .reviewx-admin-notice-badge .pending-count'
    );
  }

  function syncPendingBadge(count) {
    getBadgeCounts().forEach(function (badgeCount) {
      var badge = badgeCount.closest('.reviewx-admin-notice-badge');

      if (!badge) {
        return;
      }

      badgeCount.textContent = String(count);

      if (count > 0) {
        badge.removeAttribute('hidden');
      } else {
        badge.setAttribute('hidden', 'hidden');
      }
    });

    if (lastPendingCount !== count) {
      lastPendingCount = count;
    }

    document.dispatchEvent(
      new CustomEvent(countEventName, {
        detail: { count: count },
      })
    );
  }

  function requestPendingSummary() {
    if (!config.ajaxUrl || !config.nonce) {
      return Promise.resolve(null);
    }

    var body = new URLSearchParams();
    body.set('action', 'rvx_pending_review_summary');
    body.set('_ajax_nonce', config.nonce);

    return fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: body.toString(),
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Failed to load pending review summary.');
        }

        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data) {
          return null;
        }

        var pending = Number(payload.data.pending || 0);
        syncPendingBadge(pending);
        return payload.data;
      })
      .catch(function () {
        return null;
      });
  }

  function refreshPendingSummary() {
    if (document.hidden) {
      return;
    }

    requestPendingSummary();
  }

  syncPendingBadge(lastPendingCount);

  document.addEventListener(refreshEventName, refreshPendingSummary);
  window.addEventListener('focus', refreshPendingSummary);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
      refreshPendingSummary();
    }
  });

  if (interval > 0) {
    window.setInterval(refreshPendingSummary, interval);
  }

  refreshPendingSummary();
})();
