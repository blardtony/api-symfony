<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ProductTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetCollection()
    {
        $response = static::createClient()->request('GET', 'api/products');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:next' => '/api/products?page=2',
            ]
        ]);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testGetCollectionPagination()
    {
        $response = static::createClient()->request('GET', 'api/products?page=4');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=4',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:previous' => '/api/products?page=3',
                'hydra:next' => '/api/products?page=5',
            ]
        ]);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testCreateProduct()
    {
        static::createClient()->request('POST', '/api/products', [
            'json' => [
                'mpn' => '1234',
                'name' => 'A test product',
                'description' => 'A test description',
                'issueDate' => '1985-07-31',
                'manufacturer' => '/api/manufacturers/1'
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            'mpn' => '1234',
            'name' => 'A test product',
            'description' => 'A test description',
            'issueDate' => '1985-07-31T00:00:00+02:00'
        ]);
    }

    public function testUpdateProduct()
    {
        static::createClient()->request('PUT', '/api/products/1', [
           'json' => [
               'description' => 'An updated description',
           ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/products/1',
            'description' => 'An updated description',
        ]);
    }

    public function testCreateInvalidProduct()
    {
        static::createClient()->request('POST', '/api/products', [
            'json' => [
                'mpn' => '1234',
                'name' => 'A test product',
                'description' => 'A test description',
                'issueDate' => '1985-07-31',
                'manufacturer' => null
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'manufacturer: This value should not be null.',
        ]);
    }

}