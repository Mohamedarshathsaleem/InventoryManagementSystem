<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ProductController;
use PHPUnit\Framework\TestCase;

final class ProductControllerTest extends TestCase
{
    private ProductController $products;

    protected function setUp(): void
    {
        $this->products = new ProductController(TestDatabase::create());
    }

    public function testStoreReturnsNewIdAndIndexListsProduct(): void
    {
        $result = $this->products->store(['name' => 'USB-C Cable', 'price' => 12.5, 'stock' => 40]);

        $this->assertSame('1', (string) $result['id']);
        $this->assertSame('USB-C Cable', $this->products->index()[0]['name']);
    }

    public function testStoreRequiresName(): void
    {
        $this->assertSame(['error' => 'Product name is required'], $this->products->store(['price' => 5]));
    }

    public function testUpdateChangesOnlyProvidedFields(): void
    {
        $this->products->store(['name' => 'Mouse', 'price' => 30, 'stock' => 5]);

        $this->products->update(1, ['stock' => 12]);
        $product = $this->products->index()[0];

        $this->assertSame('Mouse', $product['name']);
        $this->assertEquals(12, $product['stock']);
    }

    public function testUpdateWithNoFieldsReturnsError(): void
    {
        $this->assertSame(['error' => 'No fields to update'], $this->products->update(1, []));
    }

    public function testDestroyRemovesProduct(): void
    {
        $this->products->store(['name' => 'Keyboard', 'price' => 99, 'stock' => 3]);

        $this->products->destroy(1);

        $this->assertSame([], $this->products->index());
    }
}
