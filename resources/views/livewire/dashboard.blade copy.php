<section class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <div><flux:heading size="xl">{{ __('Welcome, :name', ['name' => $user->name]) }}</flux:heading><flux:text>{{ $user->getRoleNames()->join(', ') ?: __('Registered user') }}</flux:text></div>

    @if ($isSuperAdmin)
        <div class="grid gap-4 md:grid-cols-2"><flux:card><flux:heading>{{ __('Active schools') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $platformStats['schools'] }}</div></flux:card><flux:card><flux:heading>{{ __('Platform users') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $platformStats['users'] }}</div></flux:card></div>
        <flux:card><div class="flex items-center justify-between gap-4"><div><flux:heading size="lg">{{ __('Platform schools') }}</flux:heading><flux:text>{{ __('Manage the participating school boundaries.') }}</flux:text></div><flux:button :href="route('schools.index')" wire:navigate variant="primary">{{ __('Manage schools') }}</flux:button></div></flux:card>
    @endif

    @if ($isSchoolAdmin)
        <div><flux:heading size="lg">{{ __('School administration') }}</flux:heading><div class="mt-4 grid gap-4 md:grid-cols-3"><flux:card><flux:heading>{{ __('Approved members') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $schoolStats['members'] }}</div></flux:card><flux:card><flux:heading>{{ __('Subjects') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $schoolStats['subjects'] }}</div></flux:card><flux:card><flux:heading>{{ __('Learning environments') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $schoolStats['environments'] }}</div></flux:card></div></div>
        <div class="grid gap-4 md:grid-cols-2">@foreach ($adminSchools as $school)<flux:card wire:key="admin-school-{{ $school->id }}"><flux:heading size="lg">{{ $school->name }}</flux:heading><div class="mt-4 flex flex-wrap gap-2"><flux:button :href="route('schools.members', $school)" wire:navigate>{{ __('Members') }}</flux:button><flux:button :href="route('schools.academic', $school)" wire:navigate>{{ __('Academic structure') }}</flux:button></div></flux:card>@endforeach</div>
    @endif

    @if ($isTeacher)
        <div><flux:heading size="lg">{{ __('Teaching workspace') }}</flux:heading><div class="mt-4 grid gap-4 md:grid-cols-4"><flux:card><flux:heading>{{ __('Assignments') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $teacherStats['assignments'] }}</div></flux:card><flux:card><flux:heading>{{ __('Assessments') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $teacherStats['assessments'] }}</div></flux:card><flux:card><flux:heading>{{ __('Submissions to review') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $teacherStats['pendingSubmissions'] }}</div></flux:card><flux:card><flux:heading>{{ __('Attempts to review') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $teacherStats['pendingAttempts'] }}</div></flux:card></div></div>
    @endif

    @if ($isStudent)
        <div><flux:heading size="lg">{{ __('Learning workspace') }}</flux:heading><div class="mt-4 grid gap-4 md:grid-cols-4"><flux:card><flux:heading>{{ __('Assignments to submit') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $studentStats['assignments'] }}</div></flux:card><flux:card><flux:heading>{{ __('Assessments to complete') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $studentStats['assessments'] }}</div></flux:card><flux:card><flux:heading>{{ __('Evaluated work') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $studentStats['evaluatedWork'] }}</div></flux:card><flux:card><flux:heading>{{ __('Engagement points') }}</flux:heading><div class="mt-2 text-3xl font-semibold">{{ $studentStats['points'] }}</div></flux:card></div></div>
    @endif

    @if ($isTeacher || $isStudent)
        <div><flux:heading size="lg">{{ $isTeacher ? __('My teaching environments') : __('My learning environments') }}</flux:heading><div class="mt-4 grid gap-4 md:grid-cols-2">@forelse($environments as $environment)<flux:card wire:key="environment-{{ $environment->id }}"><flux:heading>{{ $environment->name }}{{ $environment->section ? ' · '.$environment->section : '' }}</flux:heading><flux:text>{{ $environment->subject->name }} · {{ $environment->school->name }}</flux:text><div class="mt-4 flex flex-wrap gap-2"><flux:button :href="route('learning-environments.materials', $environment)" wire:navigate variant="primary">{{ __('Materials') }}</flux:button><flux:button :href="route('learning-environments.assignments', $environment)" wire:navigate>{{ __('Assignments') }}</flux:button><flux:button :href="route('learning-environments.assessments', $environment)" wire:navigate>{{ __('Assessments') }}</flux:button>@if ($isTeacher)<flux:button :href="route('learning-environments.ai-assistance', $environment)" wire:navigate>{{ __('AI assistant') }}</flux:button>@endif@if ($isStudent)<flux:button :href="route('performance.index')" wire:navigate>{{ __('My performance') }}</flux:button>@endif</div></flux:card>@empty<flux:card><flux:text>{{ __('You have not been assigned or enrolled in a learning environment yet.') }}</flux:text></flux:card>@endforelse</div></div>
    @endif

    @if ($isParent)
        <flux:card><flux:heading size="lg">{{ __('Guardian access') }}</flux:heading><flux:text class="mt-2">{{ __('Student linking and guardian progress visibility are not implemented yet. This dashboard intentionally does not expose any student data until that relationship exists.') }}</flux:text><flux:button class="mt-4" :href="route('schools.index')" wire:navigate>{{ __('View school access') }}</flux:button></flux:card>
    @endif
</section>
