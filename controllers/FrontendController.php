<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/SEO.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/cpt/Consultant.php';
require_once __DIR__ . '/../models/cpt/Promotion.php';
require_once __DIR__ . '/../models/cpt/Specialty.php';

/**
 * FrontendController
 * 
 * Public-facing controller for all visitor-accessible pages.
 * No authentication required. Passes SEO data to every view.
 */
class FrontendController
{
    private Post $post;
    private Page $page;
    private Setting $setting;
    private Consultant $consultant;
    private Promotion $promotion;
    private Specialty $specialty;

    public function __construct()
    {
        $this->post        = new Post();
        $this->page        = new Page();
        $this->setting     = new Setting();
        $this->consultant  = new Consultant();
        $this->promotion   = new Promotion();
        $this->specialty   = new Specialty();
    }

    /**
     * Build the default SEO data array merged with page-specific overrides.
     *
     * @param array $overrides Page-specific SEO fields
     * @return array
     */
    private function seoData(array $overrides = []): array
    {
        $defaults = [
            'title'       => $this->setting->get('default_seo_title', $this->setting->get('site_name', 'BeaconCMS')),
            'description' => $this->setting->get('default_seo_description', ''),
            'keywords'    => $this->setting->get('default_seo_keywords', ''),
            'og_image'    => '',
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Homepage: recent posts, featured consultants, active promotions.
     */
    public function home(): void
    {
        $recentPosts   = $this->post->recent(5);
        $consultants   = $this->consultant->published();
        $promotions    = $this->promotion->active();
        $settings      = $this->setting->getAllAsArray();

        $seo = $this->seoData([
            'title'       => $settings['site_name'] ?? 'Home',
            'description' => $settings['site_description'] ?? '',
        ]);

        View::render('frontend/home', [
            'pageTitle'   => 'Home',
            'seo'         => $seo,
            'recentPosts' => $recentPosts,
            'consultants' => $consultants,
            'promotions'  => $promotions,
            'settings'    => $settings,
        ]);
    }

    /**
     * Blog listing with pagination.
     */
    public function blogList(): void
    {
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;

        $result = $this->post->paginate($page, $perPage, 'status = :status', ['status' => 'published']);

        $seo = $this->seoData([
            'title'       => 'Blog',
            'description' => 'Read our latest articles and news.',
        ]);

        View::render('frontend/blog/index', [
            'pageTitle'   => 'Blog',
            'seo'         => $seo,
            'posts'       => $result['data'] ?? $result,
            'currentPage' => $page,
            'totalPages'  => $result['last_page'] ?? ceil(($result['total'] ?? count($result)) / $perPage),
            'perPage'     => $perPage,
        ]);
    }

    /**
     * Single blog post with full SEO meta.
     *
     * @param string $slug Post slug
     */
    public function blogSingle(string $slug): void
    {
        $post = $this->post->findBySlug($slug);

        if (!$post || $post['status'] !== 'published') {
            $this->notFound();
            return;
        }

        $seo = $this->seoData([
            'title'       => $post['seo_title'] ?: $post['title'],
            'description' => $post['seo_description'] ?: ($post['excerpt'] ?? ''),
            'keywords'    => $post['seo_keywords'] ?? '',
            'og_image'    => $post['og_image'] ?? ($post['featured_image'] ?? ''),
        ]);

        View::render('frontend/blog/single', [
            'pageTitle' => $post['title'],
            'seo'       => $seo,
            'post'      => $post,
        ]);
    }

    /**
     * Single CMS page with SEO meta.
     *
     * @param string $slug Page slug
     */
    public function page(string $slug): void
    {
        $page = $this->page->findBySlug($slug);

        if (!$page || $page['status'] !== 'published') {
            $this->notFound();
            return;
        }

        $template = $page['template'] ?: 'default';

        $seo = $this->seoData([
            'title'       => $page['seo_title'] ?: $page['title'],
            'description' => $page['seo_description'] ?? '',
            'og_image'    => $page['og_image'] ?? '',
        ]);

        View::render('frontend/page/' . $template, [
            'pageTitle' => $page['title'],
            'seo'       => $seo,
            'page'      => $page,
        ]);
    }

    /**
     * List all published consultants grouped by specialty.
     */
    public function consultantList(): void
    {
        $specialties = $this->specialty->published();
        $grouped     = [];

        foreach ($specialties as $spec) {
            $consultants = $this->consultant->bySpecialty((int) $spec['id']);
            if (!empty($consultants)) {
                $grouped[] = [
                    'specialty'    => $spec,
                    'consultants'  => $consultants,
                ];
            }
        }

        // Also include consultants without a specialty
        $allPublished = $this->consultant->published();
        $assignedIds  = [];
        foreach ($grouped as $group) {
            foreach ($group['consultants'] as $c) {
                $assignedIds[] = $c['id'];
            }
        }
        $unassigned = array_filter($allPublished, fn($c) => !in_array($c['id'], $assignedIds));
        if (!empty($unassigned)) {
            $grouped[] = [
                'specialty'   => ['name' => 'Other', 'slug' => 'other'],
                'consultants' => array_values($unassigned),
            ];
        }

        $seo = $this->seoData([
            'title'       => 'Our Consultants',
            'description' => 'Meet our team of expert consultants and specialists.',
        ]);

        View::render('frontend/consultants/index', [
            'pageTitle' => 'Our Consultants',
            'seo'       => $seo,
            'grouped'   => $grouped,
        ]);
    }

    /**
     * Single consultant profile with full Schema.org doctor markup.
     *
     * @param string $slug Consultant slug
     */
    public function consultantSingle(string $slug): void
    {
        $consultant = $this->consultant->findBySlug($slug);

        if (!$consultant || $consultant['status'] !== 'published') {
            $this->notFound();
            return;
        }

        // Load the specialty name for display
        if (!empty($consultant['specialty_id'])) {
            $specialtyModel = new Specialty();
            $specialty = $specialtyModel->find((int) $consultant['specialty_id']);
            $consultant['specialty_name'] = $specialty['name'] ?? '';
        } else {
            $consultant['specialty_name'] = '';
        }

        $seo = $this->seoData([
            'title'       => $consultant['seo_title'] ?: $consultant['name'],
            'description' => $consultant['seo_description'] ?: mb_substr(strip_tags($consultant['bio'] ?? ''), 0, 160),
            'og_image'    => $consultant['photo'] ?? '',
        ]);

        // Generate Schema.org Doctor structured data
        $schemaMarkup = SEO::getDoctorSchema($consultant);

        View::render('frontend/consultants/single', [
            'pageTitle'    => $consultant['name'],
            'seo'          => $seo,
            'consultant'   => $consultant,
            'schemaMarkup' => $schemaMarkup,
        ]);
    }

    /**
     * List all active promotions.
     */
    public function promotionList(): void
    {
        $promotions = $this->promotion->active();

        $seo = $this->seoData([
            'title'       => 'Promotions',
            'description' => 'Check out our current promotions and special offers.',
        ]);

        View::render('frontend/promotions/index', [
            'pageTitle'  => 'Promotions',
            'seo'        => $seo,
            'promotions' => $promotions,
        ]);
    }

    /**
     * Single promotion detail page.
     *
     * @param string $slug Promotion slug
     */
    public function promotionSingle(string $slug): void
    {
        $promotion = $this->promotion->findBySlug($slug);

        if (!$promotion || $promotion['status'] !== 'published') {
            $this->notFound();
            return;
        }

        $seo = $this->seoData([
            'title'       => $promotion['seo_title'] ?: $promotion['title'],
            'description' => $promotion['seo_description'] ?: mb_substr(strip_tags($promotion['description'] ?? ''), 0, 160),
            'og_image'    => $promotion['featured_image'] ?? '',
        ]);

        View::render('frontend/promotions/single', [
            'pageTitle' => $promotion['title'],
            'seo'       => $seo,
            'promotion' => $promotion,
        ]);
    }

    /**
     * List all specialties with consultant count.
     */
    public function specialtyList(): void
    {
        $specialties = $this->specialty->withConsultantCount();

        // Filter to only published
        $specialties = array_filter($specialties, fn($s) => $s['status'] === 'published');

        $seo = $this->seoData([
            'title'       => 'Specialties',
            'description' => 'Explore our medical specialties and areas of expertise.',
        ]);

        View::render('frontend/specialties/index', [
            'pageTitle'   => 'Specialties',
            'seo'         => $seo,
            'specialties' => array_values($specialties),
        ]);
    }

    /**
     * Single specialty page showing its consultants.
     *
     * @param string $slug Specialty slug
     */
    public function specialtySingle(string $slug): void
    {
        $specialty = $this->specialty->findBySlug($slug);

        if (!$specialty || $specialty['status'] !== 'published') {
            $this->notFound();
            return;
        }

        $consultants = $this->consultant->bySpecialty((int) $specialty['id']);

        $seo = $this->seoData([
            'title'       => $specialty['name'],
            'description' => $specialty['description'] ?? '',
        ]);

        View::render('frontend/specialties/single', [
            'pageTitle'   => $specialty['name'],
            'seo'         => $seo,
            'specialty'   => $specialty,
            'consultants' => $consultants,
        ]);
    }

    /**
     * Render the 404 Not Found page.
     */
    public function notFound(): void
    {
        http_response_code(404);

        $seo = $this->seoData([
            'title'       => 'Page Not Found',
            'description' => 'The page you are looking for could not be found.',
        ]);

        View::render('frontend/404', [
            'pageTitle' => 'Page Not Found',
            'seo'       => $seo,
        ]);
    }
}
