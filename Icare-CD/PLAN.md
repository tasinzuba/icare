# Class-Based Exercise + Explanation System — Implementation Plan

## 🎯 Overview

Branch offline students-দের জন্য একটা Class-based Exercise + Explanation system যেখানে:
- ৩০টা (বা যতগুলো প্রয়োজন) Class থাকবে
- প্রতি Class-এ Exercise (questions) আর Explanation content থাকবে
- Exercise submit না করলে Explanation locked
- Time tracking (exercise time + explanation time আলাদা)
- Per-student per-class progress tracking
- Teacher/Admin batch-wise analytics

---

## 🏗️ Architecture Decision: Existing System Reuse vs New

### Decision: **Hybrid — Existing tables + নতুন wrapper tables**

**কেন:**
1. `test_sets` table → ইতিমধ্যে questions, test structure সব আছে — এটাই class হিসেবে use হবে
2. `questions` table → `explanation`, `tips`, `common_mistakes`, `passage_reference` ইতিমধ্যে আছে
3. `student_attempts` + `student_answers` → Exercise attempt tracking ইতিমধ্যে আছে
4. `AnswerValidator` ও `ScoreCalculator` services reuse করা যাবে

**নতুন যা লাগবে:**
- `book_classes` table → Class definition (Class 1, Class 2, ... Class 30)
- `book_class_enrollments` table → কোন student কোন class set-এ enrolled
- `class_exercise_progress` table → Per-student per-class exercise tracking (time, score, status)
- `class_explanation_progress` table → Per-student per-class explanation time tracking

---

## 📊 Database Schema (New Tables)

### 1. `book_classes` — Class Definition

```sql
CREATE TABLE book_classes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,            -- "Class 1 - Listening Fundamentals"
    class_number INT NOT NULL,              -- 1, 2, 3, ... 30
    description TEXT NULL,                  -- Class description
    test_set_id BIGINT NOT NULL,            -- Links to existing test_sets table (Exercise)
    explanation_content LONGTEXT NULL,      -- Rich text explanation content (TinyMCE)
    explanation_media JSON NULL,            -- [{type: 'image/video/audio', url: '...', title: '...'}]
    branch_id BIGINT NULL,                  -- NULL = global (all branches), otherwise branch-specific
    book_set_id BIGINT NULL,               -- Groups classes into a "book" (FK to book_sets)
    order_number INT DEFAULT 0,             -- Sort order within the book
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (test_set_id) REFERENCES test_sets(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (book_set_id) REFERENCES book_sets(id),
    INDEX (class_number),
    INDEX (branch_id),
    INDEX (book_set_id)
);
```

### 2. `book_sets` — Book/Course Grouping

```sql
CREATE TABLE book_sets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,            -- "IELTS Foundation Course"
    description TEXT NULL,
    total_classes INT DEFAULT 0,            -- Auto-calculated
    branch_id BIGINT NULL,                  -- NULL = global
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (branch_id) REFERENCES branches(id),
    INDEX (branch_id)
);
```

### 3. `book_enrollments` — Student Book/Course Enrollment

```sql
CREATE TABLE book_enrollments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,                -- FK to users
    book_set_id BIGINT NOT NULL,            -- FK to book_sets
    branch_id BIGINT NOT NULL,              -- FK to branches
    enrolled_by BIGINT NULL,                -- Who enrolled them (staff user_id)
    enrolled_at TIMESTAMP DEFAULT NOW(),
    status ENUM('active', 'completed', 'inactive') DEFAULT 'active',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_set_id) REFERENCES book_sets(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    UNIQUE (user_id, book_set_id)           -- One enrollment per student per book
);
```

### 4. `class_progress` — Per-Student Per-Class Progress

```sql
CREATE TABLE class_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    book_class_id BIGINT NOT NULL,          -- FK to book_classes
    book_enrollment_id BIGINT NOT NULL,     -- FK to book_enrollments

    -- Exercise tracking
    exercise_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    exercise_attempt_id BIGINT NULL,        -- FK to student_attempts (latest completed)
    exercise_attempts_count INT DEFAULT 0,  -- Total number of attempts
    best_score DECIMAL(5,2) NULL,           -- Best correct ratio (%)
    latest_score DECIMAL(5,2) NULL,         -- Latest attempt score
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,          -- From best/latest attempt
    exercise_time_seconds INT DEFAULT 0,    -- Total exercise time across all attempts
    exercise_first_completed_at TIMESTAMP NULL,
    exercise_last_completed_at TIMESTAMP NULL,

    -- Explanation tracking
    explanation_unlocked BOOLEAN DEFAULT FALSE,
    explanation_opened_at TIMESTAMP NULL,   -- First time opened
    explanation_total_time_seconds INT DEFAULT 0, -- Active reading time
    explanation_last_accessed_at TIMESTAMP NULL,
    explanation_completed BOOLEAN DEFAULT FALSE,  -- Student marked "done reading"

    -- Overall
    status ENUM('not_started', 'exercise_in_progress', 'exercise_completed', 'completed') DEFAULT 'not_started',
    -- completed = exercise done + explanation opened (minimum)
    completed_at TIMESTAMP NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_class_id) REFERENCES book_classes(id),
    FOREIGN KEY (book_enrollment_id) REFERENCES book_enrollments(id),
    FOREIGN KEY (exercise_attempt_id) REFERENCES student_attempts(id),
    UNIQUE (user_id, book_class_id),        -- One progress record per student per class
    INDEX (book_enrollment_id),
    INDEX (status)
);
```

### 5. `class_time_logs` — Granular Time Tracking

```sql
CREATE TABLE class_time_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    book_class_id BIGINT NOT NULL,
    class_progress_id BIGINT NOT NULL,
    activity_type ENUM('exercise', 'explanation') NOT NULL,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,         -- Calculated on end
    is_active BOOLEAN DEFAULT TRUE,         -- Currently ongoing session
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_class_id) REFERENCES book_classes(id),
    FOREIGN KEY (class_progress_id) REFERENCES class_progress(id),
    INDEX (user_id, book_class_id, activity_type)
);
```

---

## 📁 File Structure (New Files to Create)

### Models (7 files):
```
app/Models/BookSet.php
app/Models/BookClass.php
app/Models/BookEnrollment.php
app/Models/ClassProgress.php
app/Models/ClassTimeLog.php
```

### Controllers (4 files):
```
app/Http/Controllers/OfflineStudent/ClassController.php          -- Student: view classes, do exercises, view explanations
app/Http/Controllers/Branch/BookClassController.php              -- Branch admin: manage book enrollments, view progress
app/Http/Controllers/Admin/BookSetController.php                 -- Admin: manage book sets and classes
app/Http/Controllers/Api/ClassTimeTrackingController.php         -- API: heartbeat for time tracking
```

### Views (12+ files):
```
-- Student Views
resources/views/offline-student/classes/index.blade.php          -- Book overview (30 classes grid)
resources/views/offline-student/classes/show.blade.php           -- Single class view (exercise + explanation)
resources/views/offline-student/classes/exercise.blade.php       -- Exercise taking page
resources/views/offline-student/classes/result.blade.php         -- Exercise result + explanation unlock
resources/views/offline-student/classes/explanation.blade.php    -- Explanation reading page (with timer)

-- Branch Admin Views
resources/views/branch/book-classes/index.blade.php              -- Book sets list
resources/views/branch/book-classes/students.blade.php           -- Student progress overview
resources/views/branch/book-classes/student-detail.blade.php     -- Individual student class-wise detail
resources/views/branch/book-classes/class-detail.blade.php       -- Single class: all students progress

-- Admin Views
resources/views/admin/book-sets/index.blade.php                  -- Book sets CRUD
resources/views/admin/book-sets/create.blade.php                 -- Create book set + assign classes
resources/views/admin/book-sets/edit.blade.php                   -- Edit book set
resources/views/admin/book-classes/create.blade.php              -- Create/edit individual class
```

### Migrations (5 files):
```
database/migrations/xxxx_create_book_sets_table.php
database/migrations/xxxx_create_book_classes_table.php
database/migrations/xxxx_create_book_enrollments_table.php
database/migrations/xxxx_create_class_progress_table.php
database/migrations/xxxx_create_class_time_logs_table.php
```

### Services (1 file):
```
app/Services/ClassExerciseService.php    -- Exercise submission, scoring, progress update logic
```

---

## 🔄 Data Flow / User Journey

### Student Flow:

```
1. Student logs in → Offline Dashboard
   └── "My Classes" section দেখবে (or separate Classes tab)
       └── Book title + progress bar (e.g., "15/30 completed")

2. Click "My Classes" → Classes Index Page
   └── 30টা class card grid দেখাবে:
       ┌──────────────────────────────┐
       │ Class 1 - Listening Basics   │
       │ ✅ Completed                  │
       │ Score: 85% | Time: 12m       │
       │ [Review]                      │
       ├──────────────────────────────┤
       │ Class 2 - Reading Part 1     │
       │ 🟡 Exercise Done, Reading... │
       │ Score: 72% | Time: 8m        │
       │ [Continue Explanation]        │
       ├──────────────────────────────┤
       │ Class 3 - Writing Task 1     │
       │ ⚪ Not Started               │
       │ [Start Exercise]             │
       ├──────────────────────────────┤
       │ Class 4 - Grammar Review     │
       │ 🔒 (previous class not done) │ ← OPTIONAL: sequential lock
       └──────────────────────────────┘

3. Click "Start Exercise" → Exercise Page
   └── Existing test-taking UI reused (questions from test_set)
   └── Timer running (exercise_time tracked)
   └── Explanation section LOCKED (blurred/hidden with lock icon)
   └── Submit → answer validation → score calculation

4. Exercise Submit → Result Page
   └── Score: 32/40 (80%)
   └── Correct ratio: 80%
   └── Time taken: 14 minutes 32 seconds
   └── Question-by-question review (correct/incorrect)
   └── 🔓 "Explanation is now unlocked!" button
   └── Option: "Retake Exercise" (if allowed)

5. Click "View Explanation" → Explanation Page
   └── Rich text content displayed
   └── Active time tracking (JS heartbeat every 30s)
   └── Questions with explanations inline
   └── Vocabulary highlights
   └── "Mark as Read" button → completes the class

6. Student Dashboard summary:
   └── Classes completed: 15/30
   └── Average score: 78%
   └── Total exercise time: 5h 23m
   └── Total explanation time: 3h 10m
```

### Teacher/Branch Admin Flow:

```
1. Branch Dashboard → "Class Progress" section
   └── Book selector dropdown
   └── Summary cards: Total students, Avg completion %, Avg score

2. Click "View Details" → Class Progress Page
   ┌──────────────────────────────────────────────────┐
   │ Batch Progress Overview                            │
   │                                                    │
   │ Total Students: 25                                 │
   │ Average Completion: 67% (20/30 classes)           │
   │ Average Score: 74%                                 │
   │                                                    │
   │ 📊 Class-wise Completion Chart                     │
   │ Class 1: ████████████████████ 100% (25/25)        │
   │ Class 2: ██████████████████   92% (23/25)          │
   │ Class 3: ████████████████     80% (20/25)          │
   │ ...                                                │
   │ Class 30: ████                 16% (4/25)          │
   │                                                    │
   │ 📋 Student-wise Progress Table                     │
   │ Student      | Completed | Avg Score | Last Active │
   │ Ali Rahman   | 25/30     | 82%       | 2h ago      │
   │ Sara Akter   | 20/30     | 74%       | 1d ago      │
   │ ...                                                │
   └──────────────────────────────────────────────────┘

3. Click student → Student Detail Page
   └── All 30 classes with individual stats
   └── Per-class: score, exercise time, explanation time, attempts, status
```

---

## 🔧 Implementation Steps (Ordered)

### Phase 1: Database & Models (Day 1)
1. Create 5 migration files
2. Create 5 model files with relationships, fillable, casts
3. Run migrations

### Phase 2: Admin — Book Set & Class Management (Day 1-2)
4. Admin BookSetController — CRUD for book sets
5. Admin views — create/edit book sets, assign test_sets as classes
6. Add admin routes
7. Explanation content editor (TinyMCE for rich text)

### Phase 3: Branch — Enrollment & Progress View (Day 2)
8. Branch BookClassController — enroll students, view progress
9. Branch views — enrollment management, progress dashboard
10. Add branch routes

### Phase 4: Student — Exercise Flow (Day 2-3)
11. ClassController — index, show, startExercise, submitExercise
12. ClassExerciseService — reuse AnswerValidator, scoring logic
13. Student exercise views — reuse existing question rendering components
14. Exercise time tracking (start_time → end_time on submit)

### Phase 5: Student — Explanation & Time Tracking (Day 3)
15. Explanation page with content display
16. JS heartbeat for active time tracking (send pulse every 30s)
17. ClassTimeTrackingController API endpoint for heartbeat
18. Explanation unlock logic (only after exercise completion)

### Phase 6: Dashboard Integration (Day 3-4)
19. Add "My Classes" section to offline student dashboard
20. Student class overview stats (completed count, avg score, total time)
21. Branch dashboard class progress section

### Phase 7: Analytics & Reports (Day 4)
22. Branch: batch-wise analytics (class completion chart, avg scores)
23. Branch: student-wise progress table
24. Branch: individual student detail view
25. Export options (optional: CSV export)

---

## 🔗 Existing Code Reuse Map

| Need | Existing Code | How to Reuse |
|---|---|---|
| Questions rendering | `student/test/reading/attempt.blade.php`, `listening/attempt.blade.php` | Extract question components, include in exercise.blade.php |
| Answer validation | `App\Services\AnswerValidator` | Direct service injection in ClassExerciseService |
| Score calculation | `App\Helpers\ScoreCalculator` | Use for correct ratio + band score |
| Student answer storage | `student_answers` table + `StudentAnswer` model | Same table, linked via `student_attempts` |
| Student attempt tracking | `student_attempts` table + `StudentAttempt` model | Create attempt with `is_practice = true` or new flag |
| Access control | `OfflineStudentAccess` middleware | Same middleware for class routes |
| File uploads (explanation media) | `HandlesFileUploads` trait | R2 CDN upload for explanation media |
| Result display | `student/results/` views | Reuse question result components |
| Explanation display | `Question::processExplanation()`, `explanation` field | Already in questions table |

---

## ⚡ Time Tracking Implementation Detail

### Exercise Time:
- **Simple:** `student_attempts.start_time` → `student_attempts.end_time`
- Already tracked by existing system
- `class_progress.exercise_time_seconds` = end_time - start_time

### Explanation Time (Active Time):
- **JS Heartbeat approach:**

```javascript
// On explanation page load
let isActive = true;
let sessionStart = Date.now();
let totalActiveTime = 0;

// Detect tab visibility
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Tab hidden → save accumulated time
        totalActiveTime += (Date.now() - sessionStart) / 1000;
        sendHeartbeat(totalActiveTime);
        isActive = false;
    } else {
        // Tab visible again → reset session
        sessionStart = Date.now();
        isActive = true;
    }
});

// Heartbeat every 30 seconds
setInterval(() => {
    if (isActive) {
        totalActiveTime += (Date.now() - sessionStart) / 1000;
        sessionStart = Date.now();
        sendHeartbeat(totalActiveTime);
    }
}, 30000);

// Before page unload
window.addEventListener('beforeunload', () => {
    if (isActive) {
        totalActiveTime += (Date.now() - sessionStart) / 1000;
        navigator.sendBeacon('/api/class-time/update', JSON.stringify({
            class_progress_id: progressId,
            explanation_time: Math.round(totalActiveTime)
        }));
    }
});
```

### API Endpoint:
```php
// POST /api/class-time/heartbeat
public function heartbeat(Request $request) {
    $request->validate([
        'class_progress_id' => 'required|exists:class_progress,id',
        'activity_type' => 'required|in:exercise,explanation',
        'duration_seconds' => 'required|integer|min:0|max:7200' // Max 2h per session
    ]);

    $progress = ClassProgress::findOrFail($request->class_progress_id);

    // Verify ownership
    if ($progress->user_id !== auth()->id()) {
        abort(403);
    }

    if ($request->activity_type === 'explanation') {
        $progress->update([
            'explanation_total_time_seconds' => $request->duration_seconds,
            'explanation_last_accessed_at' => now()
        ]);
    }

    return response()->json(['success' => true]);
}
```

---

## 🔑 Key Design Decisions

### 1. Class Content Source
- **Exercise content** → Comes from existing `test_sets` → `questions` (no duplication)
- **Explanation content** → TWO sources:
  - **Per-question explanation:** Already in `questions.explanation` field (shown in result page)
  - **Class-level explanation:** New `book_classes.explanation_content` field (overview, theory, examples)

### 2. Exercise = Existing Test System
- A class exercise IS a test_set with questions
- Student takes the exercise → creates `student_attempt` → stores `student_answers`
- Scoring uses existing `AnswerValidator` + `ScoreCalculator`
- NO new question types needed — reuse listening/reading MCQ, fill-blank, etc.

### 3. TestSet Naming Convention
- Admin creates test_sets with names like: `"Class 1 - Listening Basics Exercise"`
- Then assigns that test_set to a `book_class` record
- The `book_class.title` is the display name (e.g., "Class 1 - Listening Basics")

### 4. Sequential vs Free Order
- **Default: Free order** — student can do any class
- **Optional setting in book_sets:** `sequential_mode = true` → must complete Class N before Class N+1

### 5. Retake Policy
- Students CAN retake exercises (re-attempt)
- Each attempt creates new `student_attempt`
- `class_progress` tracks `best_score` and `latest_score`
- `exercise_attempts_count` increments

### 6. Completion Definition
- **Exercise completed** = at least one `student_attempt` with `status = 'completed'`
- **Class completed** = exercise completed + explanation opened (explanation_opened_at IS NOT NULL)
- Explanation doesn't need to be "read fully" — just opened is enough (but we track time for analytics)

---

## 🚫 NOT in Scope (Phase 1)

- Writing/Speaking exercises in classes (only objective questions — listening/reading type)
- AI evaluation for class exercises
- Payment/subscription for classes
- Gamification/badges
- Class-level discussions/comments
- Mobile app API (web only for now)
- PDF report generation
