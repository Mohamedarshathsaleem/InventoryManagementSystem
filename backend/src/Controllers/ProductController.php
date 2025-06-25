<?php
class ProductController
{
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
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute($params);

        return ['message' => 'Product updated successfully'];
    }
}