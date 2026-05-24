<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Comments\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API Controller for Comments (list + submit).
 * Replaces admin-ajax for comments on front-end.
 *
 * GET  escapezoom/v1/comments — list comments (post_id, page) → { success, data: { html, has_more, total } }
 * POST escapezoom/v1/comments — submit comment → { success, data: { html, message } } or error
 */
final class CommentsRestController
{
    private const NAMESPACE = 'escapezoom/v1';
    private const BASE = 'comments';

    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::BASE, [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'handleGetComments'],
            'permission_callback' => '__return_true',
            'args'                => [
                'post_id' => [
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ],
                'page'    => [
                    'type'              => 'integer',
                    'required'          => false,
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, self::BASE, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handlePostComment'],
            'permission_callback' => '__return_true',
            'args'                => [
                'comment_post_ID' => [
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ],
                'comment'         => [
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'rating'           => [
                    'type'              => 'integer',
                    'required'          => false,
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
                'author'           => [
                    'type'              => 'string',
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'email'            => [
                    'type'              => 'string',
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_email',
                ],
            ],
        ]);
    }

    /**
     * GET comments for a post (paginated HTML).
     */
    public function handleGetComments(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = (int) $request->get_param('post_id');
        $page    = (int) $request->get_param('page') ?: 1;

        if ($post_id <= 0) {
            return new WP_REST_Response(['success' => false, 'message' => 'Invalid Post ID'], 400);
        }

        $this->loadCommentRender();

        $per_page = 10;
        $post_type = get_post_type($post_id);
        $args = [
            'post_id' => $post_id,
            'status'  => 'approve',
            'type'    => ($post_type === 'product') ? 'review' : 'comment',
            'number'  => $per_page,
            'offset'  => ($page - 1) * $per_page,
            'order'   => 'DESC',
        ];

        $comments_query = new \WP_Comment_Query();
        $comments      = $comments_query->query($args);
        $total_comments = (int) get_comments([
            'post_id' => $post_id,
            'status'  => 'approve',
            'count'   => true,
            'type'    => $args['type'],
        ]);
        $has_more = ($page * $per_page) < $total_comments;

        $html = '';
        if ($comments) {
            foreach ($comments as $comment) {
                $html .= ez_render_comment_item_html_string($comment, $post_type);
            }
        } elseif ($page === 1) {
            $html = '<div class="text-center py-8 text-gray-500"><p>' . esc_html__('هنوز نظری ثبت نشده است.', 'escapezoom-core') . '</p></div>';
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'html'     => $html,
                'has_more' => $has_more,
                'total'    => $total_comments,
            ],
        ], 200);
    }

    /**
     * POST submit a comment.
     */
    public function handlePostComment(WP_REST_Request $request): WP_REST_Response
    {
        $comment_post_ID = (int) $request->get_param('comment_post_ID');
        $comment_content = (string) $request->get_param('comment');
        $rating          = (int) $request->get_param('rating');
        $user            = wp_get_current_user();

        if ($user->exists()) {
            $comment_author       = $user->display_name;
            $comment_author_email = $user->user_email;
            $comment_author_url   = $user->user_url;
        } else {
            $comment_author       = (string) $request->get_param('author');
            $comment_author_email = (string) $request->get_param('email');
            $comment_author_url   = '';
            if ($comment_author === '' || $comment_author_email === '') {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => __('لطفا نام و ایمیل خود را وارد کنید.', 'escapezoom-core'),
                ], 400);
            }
        }

        if ($comment_content === '') {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('متن نظر نمی‌تواند خالی باشد.', 'escapezoom-core'),
            ], 400);
        }

        $post_type = get_post_type($comment_post_ID);
        $commentdata = [
            'comment_post_ID'      => $comment_post_ID,
            'comment_content'      => $comment_content,
            'comment_author'       => $comment_author,
            'comment_author_email' => $comment_author_email,
            'comment_author_url'   => $comment_author_url,
            'comment_type'         => ($post_type === 'product') ? 'review' : 'comment',
            'comment_parent'       => 0,
            'user_id'              => $user->ID,
        ];

        $comment_id = wp_insert_comment($commentdata);

        if (!$comment_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('خطا در ثبت نظر.', 'escapezoom-core'),
            ], 500);
        }

        if ($rating > 0 && $post_type === 'product') {
            update_comment_meta($comment_id, 'rating', $rating);
        }

        $this->loadCommentRender();
        $comment = get_comment($comment_id);
        $html   = ez_render_comment_item_html_string($comment, $post_type);

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'html'    => $html,
                'message' => __('نظر شما با موفقیت ثبت شد.', 'escapezoom-core'),
            ],
        ], 200);
    }

    private function loadCommentRender(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $path = defined('EZ_CORE_PATH') ? EZ_CORE_PATH : dirname(__DIR__, 4) . '/';
        $file = $path . 'assets/stencil/components/comments/render-comments.php';
        if (is_file($file)) {
            require_once $file;
        }
        $loaded = true;
    }
}
