<?php

namespace App\Providers;

use App\Models\AiSuggestion;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\GamificationEvent;
use App\Models\LearningEnvironment;
use App\Models\LearningEnvironmentMembership;
use App\Models\LearningMaterial;
use App\Models\PerformanceRecord;
use App\Models\School;
use App\Models\SchoolMembership;
use App\Models\Subject;
use App\Policies\AiSuggestionPolicy;
use App\Policies\AssessmentAttemptPolicy;
use App\Policies\AssessmentPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\AssignmentSubmissionPolicy;
use App\Policies\GamificationEventPolicy;
use App\Policies\LearningEnvironmentMembershipPolicy;
use App\Policies\LearningEnvironmentPolicy;
use App\Policies\LearningMaterialPolicy;
use App\Policies\PerformanceRecordPolicy;
use App\Policies\SchoolMembershipPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\SubjectPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(SchoolMembership::class, SchoolMembershipPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(LearningEnvironment::class, LearningEnvironmentPolicy::class);
        Gate::policy(LearningEnvironmentMembership::class, LearningEnvironmentMembershipPolicy::class);
        Gate::policy(LearningMaterial::class, LearningMaterialPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(AssignmentSubmission::class, AssignmentSubmissionPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(AssessmentAttempt::class, AssessmentAttemptPolicy::class);
        Gate::policy(AiSuggestion::class, AiSuggestionPolicy::class);
        Gate::policy(PerformanceRecord::class, PerformanceRecordPolicy::class);
        Gate::policy(GamificationEvent::class, GamificationEventPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
