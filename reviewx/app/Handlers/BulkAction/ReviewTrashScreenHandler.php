<?php

namespace ReviewX\Handlers\BulkAction;

use ReviewX\CPT\CptHelper;
use ReviewX\Services\CacheServices;
use ReviewX\Services\ReviewService;
class ReviewTrashScreenHandler
{
    protected CptHelper $cptHelper;
    protected CacheServices $cacheServices;
    protected ReviewService $reviewService;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
        $this->cacheServices = new CacheServices();
        $this->reviewService = new ReviewService();
    }
    public function maybeHandleEmptyTrash() : void
    {
        if (!$this->isManagedTrashEmptyRequest()) {
            return;
        }
        check_admin_referer('bulk-comments');
        $deleted = 0;
        foreach ($this->getScopedTrashCommentIds() as $comment_id) {
            if ($this->reviewService->deleteCommentTreeInWp((int) $comment_id)) {
                $deleted++;
            }
        }
        $this->cacheServices->removeCache();
        \wp_safe_redirect($this->buildRedirectUrl($deleted));
        exit;
    }
    public function styleEmptyTrashButton() : void
    {
        if (!$this->isManagedTrashScreen()) {
            return;
        }
        ?>
        <style>
            .edit-comments-php .tablenav .actions input[name="delete_all"],
            .edit-comments-php .tablenav .actions input[name="delete_all2"] {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                border: 1px solid #b91c1c;
                border-radius: 999px;
                box-shadow: 0 10px 24px rgba(185, 28, 28, 0.18);
                color: #fff;
                font-weight: 600;
                letter-spacing: 0.01em;
                min-height: 34px;
                padding: 0 16px;
                text-shadow: none;
                transition: transform 0.14s ease, box-shadow 0.14s ease, filter 0.14s ease;
            }

            .edit-comments-php .tablenav .actions input[name="delete_all"]:hover,
            .edit-comments-php .tablenav .actions input[name="delete_all2"]:hover {
                box-shadow: 0 14px 28px rgba(185, 28, 28, 0.24);
                filter: brightness(1.03);
                transform: translateY(-1px);
            }

            .edit-comments-php .tablenav .actions input[name="delete_all"]:focus,
            .edit-comments-php .tablenav .actions input[name="delete_all2"]:focus {
                box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(220, 38, 38, 0.45);
                outline: none;
            }

            .edit-comments-php .tablenav .actions input[name="delete_all"]:active,
            .edit-comments-php .tablenav .actions input[name="delete_all2"]:active {
                box-shadow: 0 8px 16px rgba(185, 28, 28, 0.18);
                transform: translateY(0);
            }

            .edit-comments-php .row-actions a[data-rvx-action-loader],
            .edit-comments-php .tablenav .actions input.rvx-is-busy {
                transition: opacity 0.16s ease;
            }

            .edit-comments-php .row-actions a.rvx-is-busy,
            .edit-comments-php .tablenav .actions input.rvx-is-busy {
                opacity: 0.72;
                pointer-events: none;
            }

            .edit-comments-php .rvx-action-loader-indicator {
                animation: rvx-action-loader-spin 0.7s linear infinite;
                border: 2px solid currentColor;
                border-radius: 999px;
                border-right-color: transparent;
                display: inline-block;
                flex: 0 0 auto;
                height: 14px;
                margin-left: 8px;
                vertical-align: text-bottom;
                width: 14px;
            }

            .edit-comments-php .tablenav .actions .rvx-action-loader-indicator {
                color: #b91c1c;
                margin-left: 10px;
            }

            @keyframes rvx-action-loader-spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }
        </style>
        <?php 
    }
    public function printActionLoaderScript() : void
    {
        if (!$this->isManagedTrashScreen()) {
            return;
        }
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('comments-form');

                if (!form) {
                    return;
                }

                const busyClass = 'rvx-is-busy';
                const loaderClass = 'rvx-action-loader-indicator';
                const activeRootClass = 'rvx-review-trash-busy';
                const handledBulkActions = ['rvx_restore_publish', 'rvx_restore_pending', 'rvx_restore_spam', 'delete'];

                const isBusy = () => document.documentElement.classList.contains(activeRootClass);

                const appendLoader = (control) => {
                    const scope = control.closest('.row-actions, .actions') || control.parentElement;

                    if (!scope) {
                        return;
                    }

                    scope.querySelectorAll('.' + loaderClass).forEach((loader) => loader.remove());

                    const loader = document.createElement('span');
                    loader.className = loaderClass;
                    loader.setAttribute('aria-hidden', 'true');
                    scope.appendChild(loader);
                };

                const markBusy = (control) => {
                    if (!control || isBusy()) {
                        return;
                    }

                    document.documentElement.classList.add(activeRootClass);
                    control.classList.add(busyClass);

                    if ('disabled' in control) {
                        control.disabled = true;
                    }

                    appendLoader(control);
                };

                form.addEventListener('click', function (event) {
                    const link = event.target.closest('a[data-rvx-action-loader]');

                    if (!link) {
                        return;
                    }

                    if (isBusy()) {
                        event.preventDefault();
                        return;
                    }

                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') {
                        return;
                    }

                    markBusy(link);
                }, true);

                form.addEventListener('submit', function (event) {
                    const submitter = event.submitter;

                    if (!submitter) {
                        return;
                    }

                    if (isBusy()) {
                        event.preventDefault();
                        return;
                    }

                    if (submitter.name === 'delete_all' || submitter.name === 'delete_all2') {
                        markBusy(submitter);
                        return;
                    }

                    if (submitter.id !== 'doaction' && submitter.id !== 'doaction2') {
                        return;
                    }

                    const selectorId = submitter.id === 'doaction2'
                        ? 'bulk-action-selector-bottom'
                        : 'bulk-action-selector-top';
                    const selector = document.getElementById(selectorId);
                    const hasSelection = form.querySelectorAll('tbody .check-column input[type="checkbox"]:checked').length > 0;

                    if (!selector || !hasSelection || !handledBulkActions.includes(selector.value)) {
                        return;
                    }

                    markBusy(submitter);
                }, true);
            });
        </script>
        <?php 
    }
    private function isManagedTrashEmptyRequest() : bool
    {
        global $pagenow;
        if ($pagenow !== 'edit-comments.php') {
            return \false;
        }
        if (!$this->isManagedTrashScreen()) {
            return \false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing delete_all from $_REQUEST is safe here as it's checked by check_admin_referer later or in context
        return isset($_REQUEST['delete_all']) || isset($_REQUEST['delete_all2']);
    }
    private function isManagedTrashScreen() : bool
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing comment_status from $_REQUEST for screen detection
        $comment_status = isset($_REQUEST['comment_status']) ? \sanitize_key(\wp_unslash($_REQUEST['comment_status'])) : '';
        return $comment_status === 'trash' && $this->isReviewxCommentsScreen();
    }
    private function isReviewxCommentsScreen() : bool
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing post_type from $_REQUEST for screen detection
        $post_type = isset($_REQUEST['post_type']) ? \sanitize_key(\wp_unslash($_REQUEST['post_type'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing comment_type from $_REQUEST for screen detection
        $comment_type = isset($_REQUEST['comment_type']) ? \sanitize_key(\wp_unslash($_REQUEST['comment_type'])) : '';
        if ($comment_type === 'review') {
            return \true;
        }
        return !empty($post_type) && \in_array($post_type, $enabled_post_types, \true);
    }
    private function getScopedTrashCommentIds() : array
    {
        $query_args = ['status' => 'trash', 'fields' => 'ids', 'number' => 0, 'orderby' => 'comment_ID', 'order' => 'ASC', 'update_comment_meta_cache' => \false, 'update_comment_post_cache' => \false];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing filter parameters from $_REQUEST
        $post_type = isset($_REQUEST['post_type']) ? \sanitize_key(\wp_unslash($_REQUEST['post_type'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing filter parameters from $_REQUEST
        $comment_type = isset($_REQUEST['comment_type']) ? \sanitize_key(\wp_unslash($_REQUEST['comment_type'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Accessing filter parameters from $_REQUEST
        $pagegen_timestamp = isset($_REQUEST['pagegen_timestamp']) ? \sanitize_text_field(\wp_unslash($_REQUEST['pagegen_timestamp'])) : '';
        if (!empty($post_type)) {
            $query_args['post_type'] = $post_type;
        }
        if (!empty($comment_type) && $comment_type !== 'all') {
            $query_args['type'] = $comment_type;
        }
        if (!empty($pagegen_timestamp)) {
            $query_args['date_query'] = [['column' => 'comment_date_gmt', 'before' => $pagegen_timestamp, 'inclusive' => \false]];
        }
        return \array_map('intval', \get_comments($query_args));
    }
    private function buildRedirectUrl(int $deleted) : string
    {
        $redirect = \wp_get_referer();
        if (!$redirect) {
            $redirect = \admin_url('edit-comments.php');
        }
        $redirect = remove_query_arg(['delete_all', 'delete_all2', '_wpnonce', '_wp_http_referer', 'deleted'], $redirect);
        return \add_query_arg('deleted', $deleted, $redirect);
    }
}
