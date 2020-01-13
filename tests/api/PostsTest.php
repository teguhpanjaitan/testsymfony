<?php
// api/tests/BooksTest.php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Post;
// use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PostsTest extends ApiTestCase
{
    // This trait provided by HautelookAliceBundle will take care of refreshing the database content to a known state before each test
    // use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/api/posts');

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains([
            '@context' => '/api/contexts/Post',
            '@id' => '/api/posts',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 50,
            // 'hydra:view' => [
            //     '@id' => '/api/posts?page=1',
            //     '@type' => 'hydra:PartialCollectionView',
            //     'hydra:first' => '/api/posts?page=1',
            //     'hydra:last' => '/api/posts?page=2',
            //     'hydra:next' => '/api/posts?page=2',
            // ],
        ]);

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(30, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Post::class);
    }

    public function testCreateBook(): void
    {
        $response = static::createClient()->request('POST', '/api/posts', ['json' => [
            'slug' => 'test-1',
            'title' => 'Test 1',
            'content' => 'Brilliantly conceived and executed, this powerful evocation of twenty-first century America gives full rein to Margaret Atwood\'s devastating irony, wit and astute perception.',
            'status' => true
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Post',
            '@type' => 'Post',
            'slug' => 'test-1',
            'title' => 'Test 1',
            'content' => 'Brilliantly conceived and executed, this powerful evocation of twenty-first century America gives full rein to Margaret Atwood\'s devastating irony, wit and astute perception.',
            'status' => true
        ]);
        $this->assertRegExp('~^/api/posts/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Post::class);
    }

    // public function testCreateInvalidBook(): void
    // {
    //     static::createClient()->request('POST', '/books', ['json' => [
    //         'isbn' => 'invalid',
    //     ]]);

    //     $this->assertResponseStatusCodeSame(400);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    //     $this->assertJsonContains([
    //         '@context' => '/contexts/ConstraintViolationList',
    //         '@type' => 'ConstraintViolationList',
    //         'hydra:title' => 'An error occurred',
    //         'hydra:description' => 'isbn: This value is neither a valid ISBN-10 nor a valid ISBN-13.
    //         title: This value should not be blank.
    //         description: This value should not be blank.
    //         author: This value should not be blank.
    //         publicationDate: This value should not be null.',
    //     ]);
    // }

    public function testUpdateBook(): void
    {
        $client = static::createClient();

        // findIriBy allows to retrieve the IRI of an item by searching for some of its properties.
        // ISBN 9786644879585 has been generated by Alice when loading test fixtures.
        // Because Alice use a seeded pseudo-random number generator, we're sure that this ISBN will always be generated.
        $iri = static::findIriBy(Post::class, ['slug' => 'test-1']);

        $client->request('PUT', $iri, ['json' => [
            'title' => 'updated title',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'slug' => 'test-1',
            'title' => 'updated title',
        ]);
    }

    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(Post::class, ['slug' => 'test-1']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::$container->get('doctrine')->getRepository(Post::class)->findOneBy(['slug' => 'test-1'])
        );
    }
}