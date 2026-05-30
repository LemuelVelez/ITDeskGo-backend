<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class KnowledgeBaseController extends BaseApiController
{
    public function index()
    {
        $pagination = $this->pagination();
        $builder    = $this->db->table(SchemaModel::TABLE_KB_ARTICLES . ' a')
            ->select('a.*')
            ->orderBy('a.id', 'DESC');

        if ($this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'category_id')) {
            $builder->select('c.name AS category_name')->join(SchemaModel::TABLE_KB_CATEGORIES . ' c', 'c.id = a.category_id', 'left');
        }

        $status = $this->request->getGet('status');
        if ($status !== null && $status !== '' && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'status')) {
            $builder->where('a.status', $status);
        }

        $categoryId = $this->request->getGet('category_id');
        if ($categoryId !== null && $categoryId !== '' && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'category_id')) {
            $builder->where('a.category_id', $categoryId);
        }

        $this->applySearch($builder, $this->request->getGet('search'), ['a.title', 'a.content', 'a.summary']);

        return $this->success($this->paginateBuilder($builder, $pagination));
    }

    public function show(int $id)
    {
        $article = $this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id);

        if ($article === null) {
            return $this->notFound('Knowledge base article not found.');
        }

        return $this->success([
            'article' => $article,
        ]);
    }

    public function create()
    {
        $payload = $this->payload([
            'category_id',
            'title',
            'slug',
            'summary',
            'content',
            'status',
            'author_id',
            'published_at',
        ]);

        $errors = $this->requireFields($payload, ['title', 'content']);

        if ($errors !== []) {
            return $this->badRequest('Please complete the required article fields.', $errors);
        }

        $payload['slug']   = $payload['slug'] ?? $this->slug((string) $payload['title']);
        $payload['status'] = $payload['status'] ?? SchemaModel::KB_STATUS_DRAFT;

        if ($payload['status'] === SchemaModel::KB_STATUS_PUBLISHED && empty($payload['published_at']) && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'published_at')) {
            $payload['published_at'] = date('Y-m-d H:i:s');
        }

        $insert = $this->withTimestamps(SchemaModel::TABLE_KB_ARTICLES, $this->onlyExistingColumns(SchemaModel::TABLE_KB_ARTICLES, $payload), true);

        $this->db->table(SchemaModel::TABLE_KB_ARTICLES)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'article' => $this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id),
        ], 'Knowledge base article created successfully.');
    }

    public function update(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id) === null) {
            return $this->notFound('Knowledge base article not found.');
        }

        $payload = $this->payload([
            'category_id',
            'title',
            'slug',
            'summary',
            'content',
            'status',
            'author_id',
            'published_at',
        ]);

        if (isset($payload['title']) && empty($payload['slug']) && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'slug')) {
            $payload['slug'] = $this->slug((string) $payload['title']);
        }

        if (($payload['status'] ?? null) === SchemaModel::KB_STATUS_PUBLISHED && empty($payload['published_at']) && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'published_at')) {
            $payload['published_at'] = date('Y-m-d H:i:s');
        }

        $payload = $this->withTimestamps(SchemaModel::TABLE_KB_ARTICLES, $this->onlyExistingColumns(SchemaModel::TABLE_KB_ARTICLES, $payload));

        if ($payload === []) {
            return $this->badRequest('No valid article fields were provided.');
        }

        $this->db->table(SchemaModel::TABLE_KB_ARTICLES)->where('id', $id)->update($payload);

        return $this->success([
            'article' => $this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id),
        ], 'Knowledge base article updated successfully.');
    }

    public function delete(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id) === null) {
            return $this->notFound('Knowledge base article not found.');
        }

        $this->db->table(SchemaModel::TABLE_KB_ARTICLES)->where('id', $id)->delete();

        return $this->success([], 'Knowledge base article deleted successfully.');
    }

    public function publish(int $id)
    {
        return $this->setStatus($id, SchemaModel::KB_STATUS_PUBLISHED, 'Knowledge base article published successfully.');
    }

    public function archive(int $id)
    {
        return $this->setStatus($id, SchemaModel::KB_STATUS_ARCHIVED, 'Knowledge base article archived successfully.');
    }

    public function categories()
    {
        return $this->success([
            'categories' => $this->db->table(SchemaModel::TABLE_KB_CATEGORIES)->orderBy('name', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function createCategory()
    {
        $payload = $this->payload(['name', 'slug', 'description']);
        $errors  = $this->requireFields($payload, ['name']);

        if ($errors !== []) {
            return $this->badRequest('Please provide a category name.', $errors);
        }

        $payload['slug'] = $payload['slug'] ?? $this->slug((string) $payload['name']);
        $insert = $this->withTimestamps(SchemaModel::TABLE_KB_CATEGORIES, $this->onlyExistingColumns(SchemaModel::TABLE_KB_CATEGORIES, $payload), true);

        $this->db->table(SchemaModel::TABLE_KB_CATEGORIES)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'category' => $this->rowById(SchemaModel::TABLE_KB_CATEGORIES, $id),
        ], 'Knowledge base category created successfully.');
    }

    public function updateCategory(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_KB_CATEGORIES, $id) === null) {
            return $this->notFound('Knowledge base category not found.');
        }

        $payload = $this->payload(['name', 'slug', 'description']);

        if (isset($payload['name']) && empty($payload['slug']) && $this->tableHasColumn(SchemaModel::TABLE_KB_CATEGORIES, 'slug')) {
            $payload['slug'] = $this->slug((string) $payload['name']);
        }

        $payload = $this->withTimestamps(SchemaModel::TABLE_KB_CATEGORIES, $this->onlyExistingColumns(SchemaModel::TABLE_KB_CATEGORIES, $payload));

        if ($payload === []) {
            return $this->badRequest('No valid category fields were provided.');
        }

        $this->db->table(SchemaModel::TABLE_KB_CATEGORIES)->where('id', $id)->update($payload);

        return $this->success([
            'category' => $this->rowById(SchemaModel::TABLE_KB_CATEGORIES, $id),
        ], 'Knowledge base category updated successfully.');
    }

    public function deleteCategory(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_KB_CATEGORIES, $id) === null) {
            return $this->notFound('Knowledge base category not found.');
        }

        $this->db->table(SchemaModel::TABLE_KB_CATEGORIES)->where('id', $id)->delete();

        return $this->success([], 'Knowledge base category deleted successfully.');
    }

    private function setStatus(int $id, string $status, string $message)
    {
        if ($this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id) === null) {
            return $this->notFound('Knowledge base article not found.');
        }

        $update = ['status' => $status];

        if ($status === SchemaModel::KB_STATUS_PUBLISHED && $this->tableHasColumn(SchemaModel::TABLE_KB_ARTICLES, 'published_at')) {
            $update['published_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table(SchemaModel::TABLE_KB_ARTICLES)
            ->where('id', $id)
            ->update($this->withTimestamps(SchemaModel::TABLE_KB_ARTICLES, $update));

        return $this->success([
            'article' => $this->rowById(SchemaModel::TABLE_KB_ARTICLES, $id),
        ], $message);
    }
}
