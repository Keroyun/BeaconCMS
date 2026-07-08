<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../models/Media.php';
require_once __DIR__ . '/../models/cpt/Consultant.php';
require_once __DIR__ . '/../models/cpt/Promotion.php';

/**
 * AdminController
 * 
 * Handles the admin dashboard view with aggregate statistics.
 */
class AdminController
{
    /**
     * Display the admin dashboard with site-wide statistics.
     *
     * Shows counts for posts, pages, consultants, promotions, media,
     * and a list of recent posts.
     */
    public function dashboard(): void
    {
        if (!Auth::check()) {
            header('Location: ' . View::url('/admin/login'));
            exit;
        }

        $postModel        = new Post();
        $pageModel        = new Page();
        $consultantModel  = new Consultant();
        $promotionModel   = new Promotion();
        $mediaModel       = new Media();

        // Gather aggregate statistics
        $totalPosts       = $postModel->count();
        $totalPages       = $pageModel->count();
        $totalConsultants = $consultantModel->count();
        $totalPromotions  = $promotionModel->count();
        $totalMedia       = $mediaModel->count();

        // Recent posts for the dashboard feed
        $recentPosts = $postModel->recent(5);

        View::render('admin/dashboard', [
            'pageTitle'   => 'Dashboard',
            'stats'       => [
                'post_count'       => $totalPosts,
                'page_count'       => $totalPages,
                'consultant_count' => $totalConsultants,
                'promotion_count'  => $totalPromotions,
                'media_count'      => $totalMedia,
            ],
            'recentPosts' => $recentPosts,
        ]);
    }
}
