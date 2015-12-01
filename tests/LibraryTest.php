<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

class LibraryTest extends WebTestCase
{
    /** @test */
    public function it_adds_books_to_library()
    {
        $this->request('POST', '/books', [], ['title' => 'Domain-Driven Design', 'authors' => 'Eric Evans', 'isbn' => '0321125215']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(201);
        $this->assertThatResponseBodyContains('id');
    }

    /** @test */
    public function it_can_not_add_books_to_library_when_required_data_was_not_provided()
    {
        $this->request('POST', '/books', [], ['notExpectedData' => 'nonExpectedValue']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(400);
    }
}