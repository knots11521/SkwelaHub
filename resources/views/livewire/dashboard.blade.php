<x-page-section max-width="7xl" gap="10">

    {{-- Join School Banner --}}
    @if ($canJoinSchool)
        @if ($isGuestMode)
            <flux:card class="border-2 border-amber-200 bg-amber-50/50 dark:border-amber-800 dark:bg-amber-950/20">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400">
                            <flux:icon icon="information-circle" class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="md" class="font-bold">{{ __('Welcome to SkwelaHub') }}</flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Enter a school code or invitation to continue with your account.') }}
                            </flux:text>
                        </div>
                    </div>
                    <flux:button :href="route('join-school')" wire:navigate variant="primary" icon="building-office">
                        {{ __('Join a School') }}
                    </flux:button>
                </div>
            </flux:card>
        @else
            <flux:card class="border border-zinc-200/80 dark:border-zinc-800 bg-white/60 dark:bg-zinc-900/60">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            <flux:icon icon="building-office" class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="md" class="font-bold">{{ __('Join another classroom') }}</flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Use an invite code or link to join a classroom within your school.') }}
                            </flux:text>
                        </div>
                    </div>
                    <flux:button :href="route('join-school')" wire:navigate variant="subtle" icon="plus">
                        {{ __('Join a classroom') }}
                    </flux:button>
                </div>
            </flux:card>
        @endif
    @endif

    {{-- Hero Welcome Banner --}}
    <div
        class="relative overflow-hidden rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 text-white p-6 sm:p-8 shadow-xl">
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
        </div>
        <div class="absolute -left-10 -bottom-10 h-64 w-64 rounded-full bg-sky-500/10 blur-3xl pointer-events-none">
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span
                        class="text-xs font-semibold tracking-wider text-zinc-400 uppercase">{{ __('Dashboard') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    {{ __('Welcome back, :name', ['name' => $user->name]) }}
                </h1>
                <p class="text-sm text-zinc-300">
                    {{ __('Here is what is happening across your workspace today.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div
                    class="px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-medium text-zinc-200 shadow-inner">
                    <span class="text-zinc-400 mr-1.5">{{ __('Role:') }}</span>
                    <span
                        class="text-white font-semibold">{{ $user->getRoleNames()->join(', ') ?: __('Registered user') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Super Admin Section --}}
    @if ($isSuperAdmin)
        <section class="space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="font-bold tracking-tight">{{ __('Platform Overview') }}
                </flux:heading>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <flux:card
                    class="relative overflow-hidden group hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5 border border-zinc-200/80 dark:border-zinc-800">
                    <div class="absolute top-0 left-0 h-1 w-full bg-indigo-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                                {{ __('Active Schools') }}</flux:subheading>
                            <div class="mt-2 text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                                {{ $platformStats['schools'] }}</div>
                        </div>
                        <div
                            class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            <flux:icon icon="building-office-2" class="size-6" />
                        </div>
                    </div>
                </flux:card>

                <flux:card
                    class="relative overflow-hidden group hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5 border border-zinc-200/80 dark:border-zinc-800">
                    <div class="absolute top-0 left-0 h-1 w-full bg-sky-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                                {{ __('Platform Users') }}</flux:subheading>
                            <div class="mt-2 text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                                {{ $platformStats['users'] }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <flux:icon icon="users" class="size-6" />
                        </div>
                    </div>
                </flux:card>
            </div>

            <flux:card
                class="p-6 bg-zinc-50/50 dark:bg-zinc-900/50 border border-zinc-200/80 dark:border-zinc-800 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <flux:heading size="md" class="font-bold">{{ __('Platform Schools') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Manage boundaries, settings, and provisioning for participating schools.') }}
                    </flux:text>
                </div>
                <flux:badge color="teal" icon="building-office">{{ __('School-owned workspaces') }}</flux:badge>
            </flux:card>

            <flux:card class="overflow-x-auto p-0">
                <div class="flex items-center gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800"><flux:icon.user-group class="size-5 text-teal-600 dark:text-teal-400" /><flux:heading size="lg">{{ __('School Admin supervision') }}</flux:heading></div>
                <table class="min-w-full text-left text-sm"><thead class="bg-teal-50/60 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><tr><th class="px-5 py-3">{{ __('School Admin') }}</th><th class="px-5 py-3">{{ __('School') }}</th></tr></thead><tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">@forelse($schoolAdministrators as $administrator)<tr wire:key="platform-admin-{{ $administrator->id }}"><td class="px-5 py-4"><div class="font-medium">{{ $administrator->name }}</div><div class="text-zinc-500">{{ $administrator->email }}</div></td><td class="px-5 py-4">{{ $administrator->school?->name ?? __('No school assigned') }}</td></tr>@empty<tr><td colspan="2" class="px-5 py-8 text-center text-zinc-500">{{ __('No School Admin accounts are registered yet.') }}</td></tr>@endforelse</tbody></table>
            </flux:card>
        </section>
    @endif

    {{-- School Admin Section --}}
    @if ($isSchoolAdmin)
        <section class="space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="font-bold tracking-tight">{{ __('School Administration') }}
                </flux:heading>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <flux:card
                    class="hover:shadow-md transition-all duration-200 border border-zinc-200/80 dark:border-zinc-800">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Approved Members') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $schoolStats['members'] }}</div>
                </flux:card>

                <flux:card
                    class="hover:shadow-md transition-all duration-200 border border-zinc-200/80 dark:border-zinc-800">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Subjects') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $schoolStats['subjects'] }}</div>
                </flux:card>

                <flux:card
                    class="hover:shadow-md transition-all duration-200 border border-zinc-200/80 dark:border-zinc-800">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Learning Environments') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $schoolStats['environments'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($adminSchools as $school)
                    <flux:card wire:key="admin-school-{{ $school->id }}"
                        class="flex flex-col justify-between gap-5 border border-zinc-200/80 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">
                                    {{ $school->name }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-500">{{ __('Admin Management Portal') }}
                                </flux:text>
                            </div>
                            <flux:badge size="sm" variant="pill" color="indigo">{{ __('Active') }}</flux:badge>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:button :href="route('schools.members', $school)" wire:navigate size="sm"
                                variant="subtle" icon="users">
                                {{ __('Members') }}
                            </flux:button>
                            <flux:button :href="route('schools.academic', $school)" wire:navigate size="sm"
                                variant="subtle" icon="academic-cap">
                                {{ __('Academic Structure') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Teacher Workspace Stats --}}
    @if ($isTeacher)
        <section class="space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="font-bold tracking-tight">{{ __('Teaching Workspace') }}
                </flux:heading>
            </div>
            <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
                <flux:card
                    class="border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Assignments') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $teacherStats['assignments'] }}</div>
                </flux:card>

                <flux:card
                    class="border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Assessments') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $teacherStats['assessments'] }}</div>
                </flux:card>

                <flux:card
                    class="border border-amber-200/60 dark:border-amber-900/40 bg-amber-50/30 dark:bg-amber-950/10 hover:shadow-md transition-all duration-200">
                    <flux:subheading
                        class="text-xs uppercase tracking-wider font-semibold text-amber-700 dark:text-amber-400">
                        {{ __('Submissions to Review') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">
                        {{ $teacherStats['pendingSubmissions'] }}
                    </div>
                </flux:card>

                <flux:card
                    class="border border-amber-200/60 dark:border-amber-900/40 bg-amber-50/30 dark:bg-amber-950/10 hover:shadow-md transition-all duration-200">
                    <flux:subheading
                        class="text-xs uppercase tracking-wider font-semibold text-amber-700 dark:text-amber-400">
                        {{ __('Attempts to Review') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">
                        {{ $teacherStats['pendingAttempts'] }}
                    </div>
                </flux:card>
            </div>
            @if ($user->school)
                <flux:card class="flex items-center justify-between gap-4 border border-teal-100 dark:border-teal-900"><div><flux:heading>{{ __('Classroom and subject management') }}</flux:heading><flux:text>{{ __('Create your subjects, classrooms, and enroll students.') }}</flux:text></div><flux:button :href="route('schools.academic', $user->school)" wire:navigate variant="primary" icon="academic-cap">{{ __('Manage academics') }}</flux:button></flux:card>
            @endif
        </section>
    @endif

    {{-- Student Workspace Stats --}}
    @if ($isStudent)
        <section class="space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="font-bold tracking-tight">{{ __('Learning Workspace') }}
                </flux:heading>
            </div>
            <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
                <flux:card
                    class="border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Pending Assignments') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $studentStats['assignments'] }}</div>
                </flux:card>

                <flux:card
                    class="border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Pending Assessments') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $studentStats['assessments'] }}</div>
                </flux:card>

                <flux:card
                    class="border border-zinc-200/80 dark:border-zinc-800 hover:shadow-md transition-all duration-200">
                    <flux:subheading class="text-xs uppercase tracking-wider font-semibold text-zinc-500">
                        {{ __('Evaluated Work') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                        {{ $studentStats['evaluatedWork'] }}</div>
                </flux:card>

                <flux:card
                    class="border border-indigo-200/60 dark:border-indigo-900/40 bg-indigo-50/30 dark:bg-indigo-950/10 hover:shadow-md transition-all duration-200">
                    <flux:subheading
                        class="text-xs uppercase tracking-wider font-semibold text-indigo-700 dark:text-indigo-400">
                        {{ __('Engagement Points') }}</flux:subheading>
                    <div class="mt-2 text-3xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-400">
                        {{ $studentStats['points'] }}
                    </div>
                </flux:card>
            </div>
        </section>
    @endif

    {{-- Parent / Guardian Section --}}
    @if ($isParent)
        <section>
            <flux:card
                class="p-6 border border-zinc-200/80 dark:border-zinc-800 space-y-4 rounded-xl bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-3">
                    <div
                        class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon icon="shield-check" class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">{{ __('Guardian Access') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ __('Student relationship verification pending') }}</flux:text>
                    </div>
                </div>

                <flux:text class="max-w-2xl text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                    {{ __('Student linking and guardian progress visibility are not implemented yet. This dashboard intentionally does not expose any student data until that relationship exists.') }}
                </flux:text>
            </flux:card>
        </section>
    @endif
</x-page-section>
