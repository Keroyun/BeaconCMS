<?php
declare(strict_types=1);

class CategoryController
{
    /**
     * List all categories for a specific taxonomy type.
     */
    public function index(): void
    {
        $taxonomyType = $_GET['type'] ?? 'post_category';
        $typeInfo = Taxonomy::getType($taxonomyType);

        if (!$typeInfo) {
            View::setFlash('error', 'Invalid taxonomy type.');
            header('Location: ' . url('/admin'));
            exit;
        }

        $categories = Taxonomy::getCategoriesWithCount($taxonomyType);
        
        View::render('admin/categories/index', [
            'categories' => $categories,
            'taxonomyType' => $taxonomyType,
            'typeInfo' => $typeInfo,
            'pageTitle' => $typeInfo['label'],
        ]);
    }

    /**
     * Show create form and handle submission.
     */
    public function create(): void
    {
        $taxonomyType = $_GET['type'] ?? 'post_category';
        $typeInfo = Taxonomy::getType($taxonomyType);

        if (!$typeInfo) {
            header('Location: ' . url('/admin'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
            } else {
                $name = Sanitizer::clean($_POST['name'] ?? '');
                
                if (empty($name)) {
                    $errors[] = 'Category name is required.';
                } else {
                    $data = [
                        'name' => $name,
                        'slug' => Sanitizer::slug($_POST['slug'] ?? $name),
                        'description' => Sanitizer::clean($_POST['description'] ?? ''),
                        'taxonomy_type' => $taxonomyType,
                        'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                        'icon' => Sanitizer::clean($_POST['icon'] ?? ''),
                        'color' => Sanitizer::clean($_POST['color'] ?? ''),
                        'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    ];

                    Taxonomy::create($data);
                    View::setFlash('success', 'Category created successfully.');
                    header('Location: ' . url('/admin/categories?type=' . $taxonomyType));
                    exit;
                }
            }
        }

        // Get parent categories for dropdown
        $parentCategories = Taxonomy::getCategories($taxonomyType);

        View::render('admin/categories/form', [
            'taxonomyType' => $taxonomyType,
            'typeInfo' => $typeInfo,
            'parentCategories' => $parentCategories,
            'pageTitle' => 'Add ' . $typeInfo['singular'],
        ]);
    }

    /**
     * Show edit form and handle submission.
     */
    public function edit(int $id): void
    {
        $category = Taxonomy::find($id);

        if (!$category) {
            View::setFlash('error', 'Category not found.');
            header('Location: ' . url('/admin'));
            exit;
        }

        $taxonomyType = $category['taxonomy_type'];
        $typeInfo = Taxonomy::getType($taxonomyType);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
            } else {
                $name = Sanitizer::clean($_POST['name'] ?? '');
                
                if (empty($name)) {
                    $errors[] = 'Category name is required.';
                } else {
                    $data = [
                        'name' => $name,
                        'slug' => Sanitizer::slug($_POST['slug'] ?? $name),
                        'description' => Sanitizer::clean($_POST['description'] ?? ''),
                        'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                        'icon' => Sanitizer::clean($_POST['icon'] ?? ''),
                        'color' => Sanitizer::clean($_POST['color'] ?? ''),
                        'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    ];

                    Taxonomy::update($id, $data);
                    View::setFlash('success', 'Category updated successfully.');
                    header('Location: ' . url('/admin/categories?type=' . $taxonomyType));
                    exit;
                }
            }
        }

        // Get potential parents (exclude self and children in a real app, keeping it simple here)
        $parentCategories = array_filter(Taxonomy::getCategories($taxonomyType), function($c) use ($id) {
            return $c['id'] != $id; 
        });

        View::render('admin/categories/form', [
            'category' => $category,
            'taxonomyType' => $taxonomyType,
            'typeInfo' => $typeInfo,
            'parentCategories' => $parentCategories,
            'pageTitle' => 'Edit ' . $typeInfo['singular'],
        ]);
    }

    /**
     * Delete a category.
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            $category = Taxonomy::find($id);
            if ($category) {
                Taxonomy::delete($id);
                View::setFlash('success', 'Category deleted successfully.');
                header('Location: ' . url('/admin/categories?type=' . $category['taxonomy_type']));
                exit;
            }
        }
        
        View::setFlash('error', 'Delete failed.');
        header('Location: ' . url('/admin'));
        exit;
    }
}
