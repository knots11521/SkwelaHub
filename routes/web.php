<?php

use App\Livewire\Academic\Manage as AcademicManage;
use App\Livewire\Ai\Assistant as AiAssistant;
use App\Livewire\Assessments\Index as AssessmentIndex;
use App\Livewire\Assessments\Results as AssessmentResults;
use App\Livewire\Assignments\Index as AssignmentIndex;
use App\Livewire\Assignments\Submissions as AssignmentSubmissions;
use App\Livewire\Dashboard;
use App\Livewire\LearningEnvironments\ManageMembers;
use App\Livewire\LearningMaterials\Index as LearningMaterialsIndex;
use App\Livewire\MembershipRequests;
use App\Livewire\Performance\Index as PerformanceIndex;
use App\Livewire\Schools\Index as SchoolIndex;
use App\Livewire\Schools\Members as SchoolMembers;
use App\Livewire\Assessments\Take as AssessmentTake;
use App\Livewire\Assessments\Edit as AssessmentEdit;
use App\Livewire\Assessments\GlobalIndex as GlobalAssessmentsIndex;
use App\Livewire\Assignments\GlobalIndex as GlobalAssignmentsIndex;
use App\Livewire\LearningMaterials\GlobalIndex as GlobalMaterialsIndex;
use App\Livewire\LearningEnvironments\Index as LearningEnvironmentsIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');


    Route::livewire('my-assessments', GlobalAssessmentsIndex::class)->name('assessments.index');
    Route::livewire('my-assignments', GlobalAssignmentsIndex::class)->name('assignments.index');
    Route::livewire('my-materials', GlobalMaterialsIndex::class)->name('materials.index');

    Route::livewire('schools', SchoolIndex::class)->name('schools.index');
    Route::livewire('membership-requests', MembershipRequests::class)->name('membership-requests.index');
    Route::livewire('schools/{school}/members', SchoolMembers::class)->name('schools.members');
    Route::livewire('schools/{school}/academic', AcademicManage::class)->name('schools.academic');
    Route::livewire('/learning-environments', LearningEnvironmentsIndex::class)->name('learning-environments.index');
    Route::livewire('learning-environments/{learningEnvironment}/members', ManageMembers::class)->name('learning-environments.members');
    Route::livewire('learning-environments/{learningEnvironment}/materials', LearningMaterialsIndex::class)->name('learning-environments.materials');
    Route::livewire('learning-environments/{learningEnvironment}/assignments', AssignmentIndex::class)->name('learning-environments.assignments');
    Route::livewire('assignments/{assignment}/submissions', AssignmentSubmissions::class)->name('assignments.submissions');
    Route::livewire('learning-environments/{learningEnvironment}/assessments', AssessmentIndex::class)->name('learning-environments.assessments');
    Route::get('/assessments/{assessment}/edit', AssessmentEdit::class)
        ->name('assessments.edit');
    Route::get('/assessments/{assessment}/take', AssessmentTake::class)
        ->name('assessments.take');
    Route::livewire('learning-environments/{learningEnvironment}/ai-assistance', AiAssistant::class)->name('learning-environments.ai-assistance');
    Route::livewire('assessments/{assessment}/results', AssessmentResults::class)->name('assessments.results');
    Route::livewire('my-performance', PerformanceIndex::class)->name('performance.index');
});

require __DIR__ . '/settings.php';
