# V1 Development Phases
## Multi-School Educational Learning and Performance Management System

This roadmap breaks the V1 Development Guidelines into sequential, professional build phases. Each phase has a single clear objective, explicit deliverables, and an explicit "do not do yet" boundary — consistent with the document's principle that a smaller, correctly-implemented system beats a larger, half-finished one.

## Phase 1 — Foundation Configuration: Auth Skeleton & Spatie Permission
**Objective:** Stand up identity and the permission engine, since both require actual configuration work.

- Install and configure Spatie Laravel Permission: publish config/migrations, run migrations, register the trait on the User model.
- Implement base authentication (registration, login, logout, password reset) per §5 — registration creates an **identity only**, not access.
- Define the five V1 roles (§4) in seeders: Super Admin, School Admin, Teacher, Student, Parent/Guardian — as records, not yet wired to any business logic.
- Establish base `users` table adjustments needed platform-wide (independent of any school).

**Exit criteria:** A user can register/login; roles exist in the database via Spatie; no school or membership logic exists yet.

---

## Phase 2 — Multi-School Core & Membership Lifecycle
**Objective:** Implement §7–§9: schools as isolated boundaries and the request → pending → approved membership flow.

- `schools` table/model (Super Admin managed).
- `school_memberships` (or similar) modeling User ↔ School with status: Pending, Approved, Rejected, Suspended, Removed (§8).
- Membership request workflow (§5, §38 steps 1–5).
- Learning-environment membership modeled as a **separate** relationship from school membership (§9), tables only — logic comes in Phase 4.
- Global authorization scaffolding: policies/gates that check role **and** school membership **and** status together (§6, §30), not role alone.

**Exit criteria:** A user can request to join a school; a School/Super Admin can approve/reject; only Approved members are treated as "in" the school anywhere in the app.

---

## Phase 3 — Academic Structure
**Objective:** Implement §10: School → Subject → Class/Learning Environment, each explicitly owned by a school (§29, §37).

- `subjects`, `classes` (or `learning_environments` shell) tables, each with a mandatory `school_id`.
- School Admin CRUD for academic structure, scoped strictly to their own school.
- Cross-school access-denial tests written now, before more domains are added (§40 "School Isolation" tests).

**Exit criteria:** Two seeded schools can each manage their own subjects/classes; School A cannot see or touch School B's structure via any route or query.

---

## Phase 4 — Learning Environments & Membership
**Objective:** Implement §11 and finish §9's learning-environment membership.

- Teacher assignment to a learning environment.
- Student enrollment into a learning environment (separate from school membership).
- Learning-environment becomes the primary classroom-level authorization boundary going forward.

**Exit criteria:** A teacher sees only their assigned environments; a student sees only enrolled environments; enrollment does not leak from school-level approval alone.

---

## Phase 5 — Learning Materials
**Objective:** Implement §12.

- Teachers create/manage materials (text lessons, links, uploads) scoped to a learning environment.
- Students can view only materials in environments they're enrolled in.

**Exit criteria:** Access-control tests pass for materials visibility across roles and environments.

---

## Phase 6 — Assignments & Submissions
**Objective:** Implement §13 and §15, the full assignment lifecycle.

- Teacher creates/publishes assignments tied to a learning environment.
- Student views and submits work; submission tied to student + assignment + environment + attempt/status.
- Enforce: a student cannot submit on another student's behalf; a teacher can only see submissions in their own environments.

**Exit criteria:** End-to-end assignment flow works for one environment, with authorization boundaries tested.

---

## Phase 7 — Quizzes/Assessments
**Objective:** Implement §14 using the same pattern established in Phase 6.

- Teacher creates/publishes assessments; student attempts; responses recorded; results become available.
- Keep scope to the core mechanism — not every possible exam feature (§14, §36).

**Exit criteria:** A student can complete a quiz and see a result; teacher sees aggregate responses for their environment only.

---

## Phase 8 — Evaluation, Feedback & Performance Records
**Objective:** Implement §16–§18.

- Teacher grading/evaluation UI for assignments and assessments; feedback field; evaluation status/timestamp.
- Performance records derived from actual assignment/quiz/participation data (§17) — not manually entered.
- Data-integrity rules: students, parents, and unauthorized teachers cannot modify results; teachers can only modify results in their own environments (§18).

**Exit criteria:** Full cycle — submit → evaluate → feedback → performance record — works, with modification rights locked down and tested.

---

## Phase 9 — Basic Gamification
**Objective:** Implement §19–§20, kept intentionally simple.

- Points, achievements, basic milestones tied to completed learning activity.
- Explicitly keep gamification score and academic performance as separate, non-interchangeable data (§20).

**Exit criteria:** Completing an assignment/quiz can trigger a point/achievement; no gamification value ever substitutes for a grade anywhere in the UI or data model.

---

## Phase 10 — Teacher-Assisted AI
**Objective:** Implement §21–§23, the human-oversight AI workflow.

- Teacher-initiated AI requests only (quiz ideas, assignment ideas, rubric suggestions, etc.).
- AI output stored/rendered as draft/suggestion — never auto-published, never auto-grading (§22–§23).
- Explicit teacher review → modify → approve → publish steps required before any AI content reaches students.

**Exit criteria:** AI can generate a draft; nothing reaches a student without an explicit teacher publish action; tests confirm AI cannot bypass this.

---

## Phase 11 — Dashboards
**Objective:** Implement §24–§28, one per role, using data now already flowing through the system.

- Teacher, Student, School Admin, Super Admin, Parent/Guardian dashboards — each scoped to what that role is authorized to see.
- Parent/Guardian dashboard stays deliberately minimal per §28.

**Exit criteria:** Each role sees a dashboard containing only its own authorized data; no dashboard leaks another school's or another student's information.

---

## Phase 12 — Authorization Hardening, UI Pass & Testing
**Objective:** Close out with §31, §32, and §39–§41.

- Full policy-first audit: every protected resource (schools, memberships, environments, assignments, submissions, assessments, results, performance, AI content) has server-side authorization, not just UI hiding.
- Direct-object-reference tests (`/user/assignment/123` style) across all roles (§39).
- Apply the Teal/Roboto/Flux UI visual system consistently (§32) — this is polish, done last, not before the workflow is correct.
- Run the full V1 test matrix from §40: auth, school isolation, membership states, role authorization, learning workflow, performance integrity, AI oversight.

**Exit criteria (= V1 Completion Standard, §41):** Two or more schools can independently run the full cycle — membership → structure → environment → materials → assignment/assessment → submission → evaluation → feedback → performance → gamification → teacher AI assistance → continued learning — with no cross-school leakage and no role overreach.

---

## Why Spatie Is Deferred to Phase 1, Not Phase 0
Phase 0 is a pure toolchain step: installing binaries and packages so the environment can run. Spatie Laravel Permission isn't usable the moment it's installed — it requires publishing its config/migration files and running migrations against a live schema before it does anything, which is configuration/setup work tied to the identity domain (§5–§6). Livewire and Flux UI, by contrast, are installed and are immediately part of the running shell with no domain-specific setup required, so they belong in Phase 0.
