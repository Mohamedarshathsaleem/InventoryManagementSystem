<?php
namespace App\Controllers;

use App\db;

class ProductController
{
    private $db;

    public function __construct(db $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        $stmt = $this->db->getPDO()->prepare("SELECT id, name, price, stock FROM products");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function store($data)
    {
        $name = $data['name'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;

        if (!$name) {
            return ['error' => 'Product name is required'];
        }

        $stmt = $this->db->getPDO()->prepare(
            "INSERT INTO products (name, price, stock, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([$name, $price, $stock]);

        return ['message' => 'Product added successfully', 'id' => $this->db->getPDO()->lastInsertId()];
    }

    public function update($id, $data)
    {
        // Only update fields that are present in $data
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
        }
        if (isset($data['price'])) {
            $fields[] = 'price = ?';
            $params[] = $data['price'];
        }
        if (isset($data['stock'])) {
            $fields[] = 'stock = ?';
            $params[] = $data['stock'];
        }

        if (empty($fields)) {
            return ['error' => 'No fields to update'];
        }

        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute($params);

        return ['message' => 'Product updated successfully'];
    }

    public function destroy($id)
    {
        $stmt = $this->db->getPDO()->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return ['message' => 'Product deleted successfully'];
    }
}