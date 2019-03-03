<?php

namespace Tests\Feature\Projects;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectIssuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_project_issues()
    {
        $this->adminUserSigningIn();
        $project = factory(Project::class)->create();
        $issue = factory(Issue::class)->create([
            'project_id' => $project->id,
            'title'      => 'The issue title.',
            'body'       => 'This is a project issue body.',
        ]);

        $this->visitRoute('projects.issues.index', $project);
        $this->seeRouteIs('projects.issues.index', $project);

        $this->seeText('The issue title.');
        $this->seeText('This is a project issue.');
    }

    /** @test */
    public function admin_can_add_issue_to_a_project()
    {
        $admin = $this->adminUserSigningIn();
        $project = factory(Project::class)->create();

        $this->visitRoute('projects.issues.index', $project);

        $this->submitForm(__('issue.create'), [
            'title' => 'First Issue.',
            'body'  => 'First Issue description.',
        ]);

        $this->seePageIs(route('projects.issues.index', $project));
        $this->see(__('issue.created'));

        $this->seeInDatabase('issues', [
            'project_id' => $project->id,
            'title'      => 'First Issue.',
            'body'       => 'First Issue description.',
            'creator_id' => $admin->id,
        ]);
    }

    /** @test */
    public function user_can_edit_issue()
    {
        $this->adminUserSigningIn();
        $project = factory(Project::class)->create();
        $issue = factory(Issue::class)->create([
            'project_id' => $project->id,
            'title'      => 'The issue title.',
            'body'       => 'This is a project issue body.',
        ]);

        $this->visitRoute('projects.issues.index', $project);
        $this->seeElement('a', ['id' => 'edit-issue-'.$issue->id]);
        $this->click('edit-issue-'.$issue->id);
        $this->seeRouteIs('projects.issues.index', [$project, 'action' => 'issue-edit', 'issue_id' => $issue->id]);

        $this->submitForm(__('issue.update'), [
            'title' => 'First Issue.',
            'body'  => 'This is a project issue body.',
        ]);

        $this->seePageIs(route('projects.issues.index', $project));
        $this->see(__('issue.updated'));

        $this->seeInDatabase('issues', [
            'id'         => $issue->id,
            'project_id' => $project->id,
            'title'      => 'First Issue.',
            'body'       => 'This is a project issue body.',
        ]);
    }

    /** @test */
    public function user_can_delete_issue()
    {
        $this->adminUserSigningIn();
        $project = factory(Project::class)->create();
        $issue = factory(Issue::class)->create([
            'project_id' => $project->id,
        ]);

        $this->visitRoute('projects.issues.index', $project);
        $this->seeElement('button', ['id' => 'delete-issue-'.$issue->id]);
        $this->press('delete-issue-'.$issue->id);

        $this->seePageIs(route('projects.issues.index', $project));
        $this->see(__('issue.deleted'));

        $this->dontSeeInDatabase('issues', [
            'id' => $issue->id,
        ]);
    }
}
