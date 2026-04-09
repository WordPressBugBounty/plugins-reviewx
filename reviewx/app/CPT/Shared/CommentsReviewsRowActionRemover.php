<?php

namespace ReviewX\CPT\Shared;

use ReviewX\CPT\CptHelper;
class CommentsReviewsRowActionRemover
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Remove specific row actions from comments and reviews based on the post type and comment type.
     *
     * @param array      $actions The array of actions for the comment row.
     * @param WP_Comment $comment The comment object.
     * @return array The modified actions array.
     */
    public function removeCommentsReviewsRowActions($actions, $comment)
    {
        if (!$this->isReviewxManagedComment($comment)) {
            return $actions;
        }
        if (isset($actions['approve'])) {
            $actions['approve'] = $this->replaceActionLabel($actions['approve'], \__('Publish', 'reviewx'));
        }
        if (isset($actions['unapprove'])) {
            $actions['unapprove'] = $this->replaceActionLabel($actions['unapprove'], \__('Mark as Pending', 'reviewx'));
        }
        foreach (['reply', 'quickedit', 'edit'] as $action) {
            if (isset($actions[$action])) {
                unset($actions[$action]);
            }
        }
        if (wp_get_comment_status($comment) === 'trash') {
            return $this->buildTrashActions($actions, $comment);
        }
        return $actions;
    }
    private function isReviewxManagedComment($comment) : bool
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        $post_type = \get_post_type($comment->comment_post_ID);
        return \in_array($post_type, $enabled_post_types, \true) && \in_array($comment->comment_type, ['comment', 'review'], \true);
    }
    private function buildTrashActions(array $actions, $comment) : array
    {
        $trash_actions = ['approve' => $this->buildCommentActionLink($comment, 'approvecomment', \__('Restore as Published', 'reviewx'), \__('Restore this review as published', 'reviewx')), 'unapprove' => $this->buildCommentActionLink($comment, 'unapprovecomment', \__('Restore as Pending', 'reviewx'), \__('Restore this review as pending', 'reviewx')), 'spam' => $this->buildCommentActionLink($comment, 'spamcomment', \__('Restore as Spam', 'reviewx'), \__('Restore this review as spam', 'reviewx'))];
        if (isset($actions['delete'])) {
            $trash_actions['delete'] = $actions['delete'];
        }
        return $trash_actions;
    }
    private function buildCommentActionLink($comment, string $action, string $label, string $aria_label) : string
    {
        $nonce_key = \in_array($action, ['approvecomment', 'unapprovecomment'], \true) ? 'approve-comment_' : 'delete-comment_';
        $url = \add_query_arg(['action' => $action, 'c' => $comment->comment_ID], \admin_url('comment.php'));
        $url = wp_nonce_url($url, $nonce_key . $comment->comment_ID);
        return \sprintf('<a href="%s" class="rvx-comment-action-link" data-rvx-action-loader="row-action" aria-label="%s">%s</a>', \esc_url($url), \esc_attr($aria_label), \esc_html($label));
    }
    private function replaceActionLabel(string $action_html, string $label) : string
    {
        return \preg_replace('/>[^<]+<\\/a>$/', '>' . \esc_html($label) . '</a>', $action_html) ?? $action_html;
    }
}
