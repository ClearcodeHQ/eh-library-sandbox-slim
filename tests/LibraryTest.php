<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

class LibraryTest extends WebTestCase
{
    /** @test */
    public function it_adds_books_to_library()
    {
        $this->request('PUT', '/books/e513f21c-a976-450e-a18c-26b696e53326', ['title' => 'Domain-Driven Design', 'authors' => 'Eric Evans', 'isbn' => '0321125215']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(201);
        $this->assertArrayHasKey('id', $this->jsonResponseData);
    }

    /** @test */
    public function it_can_not_add_books_to_library_when_required_data_was_not_provided()
    {
        $this->request('PUT', '/books/e513f21c-a976-450e-a18c-26b696e53326', ['nonExpectedData']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(400);
    }

    /** @test */
    public function it_list_all_books_in_library()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');
        $this->addBook('38483e7a-e815-4657-bc94-adc83047577e', 'REST in Practice', 'Jim Webber, Savas Parastatidis, Ian Robinson', '978-0596805821');

        $this->request('GET', '/books');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
        $this->assertCount(2, $this->jsonResponseData);
    }

    /** @test */
    public function it_paginates_books_in_library()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');
        $this->addBook('38483e7a-e815-4657-bc94-adc83047577e', 'REST in Practice', 'Jim Webber, Savas Parastatidis, Ian Robinson', '978-0596805821');
        $this->addBook('979b4f4e-6c87-456a-a8b3-be6cff32b660', 'Clean Code', 'Robert C. Martin ', '978-0132350884');

        $this->request('GET', '/books?page=2&booksPerPage=2');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
        $this->assertCount(1, $this->jsonResponseData);
    }

    /** @test */
    public function it_return_empty_list_when_no_books_in_library()
    {
        $this->request('GET', '/books');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
        $this->assertCount(0, $this->jsonResponseData);
    }

    /** @test */
    public function it_creates_reservation_for_book()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');

        $this->request('POST', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations', ['email' => 'employee.@clearcode.cc']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(201);
    }

    /** @test */
    public function it_can_not_create_reservation_without_email()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');

        $this->request('POST', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations', ['nonExpectedData']);

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(400);
    }

    /** @test */
    public function it_can_give_away_book_from_reservation()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');
        $this->addReservation('8cb7aa6f-f09c-4287-86af-013abf630fc8', 'a7f0a5b1-b65a-4f9b-905b-082e255f6038');

        $this->request('PATCH', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations/8cb7aa6f-f09c-4287-86af-013abf630fc8');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
    }

    /** @test */
    public function it_can_not_give_away_book_already_given_away()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');
        $this->addReservation('8cb7aa6f-f09c-4287-86af-013abf630fc8', 'a7f0a5b1-b65a-4f9b-905b-082e255f6038', true);

        $this->request('PATCH', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations/8cb7aa6f-f09c-4287-86af-013abf630fc8');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(400);
    }

    /** @test */
    public function it_can_give_back_book_from_reservation()
    {
        $this->addBook('a7f0a5b1-b65a-4f9b-905b-082e255f6038', 'Domain-Driven Design', 'Eric Evans', '0321125215');
        $this->addReservation('8cb7aa6f-f09c-4287-86af-013abf630fc8', 'a7f0a5b1-b65a-4f9b-905b-082e255f6038', true);

        $this->request('DELETE', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations/8cb7aa6f-f09c-4287-86af-013abf630fc8');

        $this->assertThatResponseHasNotContentType();
        $this->assertThatResponseHasStatus(204);
    }

    /** @test */
    public function it_can_list_reservations_for_book()
    {
        $this->addReservation('8cb7aa6f-f09c-4287-86af-013abf630fc8', 'a7f0a5b1-b65a-4f9b-905b-082e255f6038');

        $this->request('GET', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
        $this->assertCount(1, $this->jsonResponseData);
    }

    /** @test */
    public function it_returns_empty_list_when_no_reservations_for_book()
    {
        $this->request('GET', '/books/a7f0a5b1-b65a-4f9b-905b-082e255f6038/reservations');

        $this->assertThatResponseHasContentType('application/json');
        $this->assertThatResponseHasStatus(200);
        $this->assertCount(0, $this->jsonResponseData);
    }
}
