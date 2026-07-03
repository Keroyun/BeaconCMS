<?php
$pageTitle = 'Blog';
$seoData = $seoData ?? ['title' => 'Blog', 'description' => 'Latest health news, tips and articles from Beacon Hospital'];
ob_start();
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo url('/'); ?>">Home</a>
            <span class="separator">/</span>
            <span>Blog</span>
        </nav>
        <h1>Our Blog</h1>
        <p>Health tips, medical news, and updates from Beacon Hospital</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($posts)): ?>
            <div class="empty-section">
                <i class="fa-solid fa-newspaper"></i>
                <h3>No Posts Yet</h3>
                <p>Blog articles coming soon. Stay tuned!</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card">
                        <div class="blog-image">
                            <?php if (!empty($post['featured_image'])): ?>
                                <a href="<?php echo url('/blog/' . $post['slug']); ?>">
                                    <img src="<?php echo he(url('/' . $post['featured_image'])); ?>" alt="<?php echo he($post['title']); ?>" loading="lazy">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo url('/blog/' . $post['slug']); ?>" class="blog-image-placeholder">
                                    <i class="fa-solid fa-newspaper"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i class="fa-solid fa-calendar"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <h3><a href="<?php echo url('/blog/' . $post['slug']); ?>"><?php echo he($post['title']); ?></a></h3>
                            <?php if (!empty($post['excerpt'])): ?>
                                <p><?php echo he(substr($post['excerpt'], 0, 150)); ?>...</p>
                            <?php elseif (!empty($post['content'])): ?>
                                <p><?php echo he(substr(strip_tags($post['content']), 0, 150)); ?>...</p>
                            <?php endif; ?>
                            <a href="<?php echo url('/blog/' . $post['slug']); ?>" class="read-more">Read More <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (($totalPages ?? 1) > 1): ?>
                <nav class="pagination">
                    <?php if (($currentPage ?? 1) > 1): ?>
                        <a href="<?php echo url('/blog?page=' . ($currentPage - 1)); ?>" class="page-btn">
                            <i class="fa-solid fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?php echo url('/blog?page=' . $i); ?>" class="page-btn <?php echo $i == ($currentPage ?? 1) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if (($currentPage ?? 1) < $totalPages): ?>
                        <a href="<?php echo url('/blog?page=' . ($currentPage + 1)); ?>" class="page-btn">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
