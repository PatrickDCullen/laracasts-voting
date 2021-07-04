<?php

namespace Tests\Feature\Comments;

use Tests\TestCase;
use App\Models\Idea;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowCommentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function idea_comments_livewire_component_renders()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('idea-comments');
    }

    /** @test */
    public function idea_comment_livewire_component_renders()
    {
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Comment body'
        ]);

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('idea-comment');
    }

    /** @test */
    public function idea_comments_livewire_component_shows_appropriate_message_when_idea_has_no_comments()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('idea-comment')
            ->assertSee('No comments yet');
    }

    /** @test */
    public function list_of_comments_shows_on_idea_page()
    {
        $idea = Idea::factory()->create();

        $commentOne = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First comment body'
        ]);

        $commentTwo = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Second comment body'
        ]);

        $this->get(route('idea.show', $idea))
            ->assertSeeInOrder([$commentOne->body, $commentTwo->body])
            ->assertSee('2 comments');
    }

    /** @test */
    public function comments_count_shows_correctly_on_index_page()
    {
        $idea = Idea::factory()->create();

        $commentOne = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First comment body'
        ]);

        $commentTwo = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Second comment body'
        ]);

        $this->get(route('idea.index'))
            ->assertSee('2 comments');
    }

    /** @test */
    public function op_badge_shows_if_author_of_idea_comments_on_idea()
    {
        $user = User::factory()->create();

        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $commentOne = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First comment body'
        ]);

        $commentTwo = Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
            'body' => 'Second comment body'
        ]);

        $this->get(route('idea.show', $idea))
            ->assertSee('OP');
    }

    /** @test */
    public function comments_pagination_works()
    {
        $idea = Idea::factory()->create();

        $commentOne = Comment::factory()->create([
            'idea_id' => $idea->id,
        ]);

        Comment::factory($commentOne->getPerPage())->create([
            'idea_id' => $idea->id,
        ]);

        $response = $this->get(route('idea.show', $idea));

        $response->assertSee($commentOne->body);
        $response->assertDontSee(Comment::find(Comment::count())->body);

        $response = $this->get(route('idea.show', [
            'idea' => $idea,
            'page' => 2,
        ]));

        $response->assertDontSee($commentOne->body);
        $response->assertSee(Comment::find(Comment::count())->body);
    }
}
