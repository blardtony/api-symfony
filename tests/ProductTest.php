<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = '4cd2ea0a0eec08079141c248ebb2e13d671f432db9c331b9520d3c77682e90ad9c63324fb9f87fbdef05cc5581c0c06d366e64c12a0ffca4fa077762';

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('tony.blard@orange.com')->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = (new ApiToken())->setToken(self::API_TOKEN)->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();
    }


    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetCollection()
    {
        $response = $this->client->request('GET', 'api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);
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
        $response = $this->client->request('GET', 'api/products?page=4', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);
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
        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
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
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => self::API_TOKEN],
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
        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
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

    public function testInvalidToken()
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => 'fake'],
            'json' => [
                'description' => 'An updated description',
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'message' => 'Invalid credentials.'
        ]);
    }

}